<?php
defined('ABSPATH') || exit;

class WC_EInvoice_Logger {
    private $log_enabled = false;
    private $log_file = '';
    
    public function __construct() {
        $settings = get_option('wc_einvoice_settings', array());
        $this->log_enabled = !empty($settings['enable_logging']);
        $this->log_file = WC_LOG_DIR . 'einvoice-' . date('Y-m-d') . '.log';
        
        // Initialize logging
        add_action('init', array($this, 'init_logging'));
        
        // Register cleanup routine
        add_action('wp_scheduled_delete', array($this, 'cleanup_logs'));
    }

    /**
     * Initialize logging system
     */
    public function init_logging() {
        if (!$this->log_enabled) {
            return;
        }

        if (!file_exists(WC_LOG_DIR)) {
            @mkdir(WC_LOG_DIR);
        }
    }

    /**
     * Log message with context
     */
    public function log($message, $context = array(), $level = 'info') {
        if (!$this->log_enabled) {
            return;
        }

        $timestamp = current_time('c');
        $formatted_message = sprintf(
            '[%s] %s: %s %s',
            $timestamp,
            strtoupper($level),
            $message,
            !empty($context) ? json_encode($context) : ''
        );

        error_log($formatted_message . PHP_EOL, 3, $this->log_file);

        // Allow external logging systems to hook in
        do_action('wc_einvoice_logged_message', $message, $context, $level);
    }

    /**
     * Log error with stack trace
     */
    public function log_error($message, $exception = null) {
        $context = array();
        
        if ($exception instanceof Exception) {
            $context['exception'] = array(
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            );
        }

        $this->log($message, $context, 'error');
        
        // Notify admin if critical error
        if ($this->is_critical_error($message)) {
            $this->notify_admin($message, $context);
        }
    }

    /**
     * Determine if error is critical
     */
    private function is_critical_error($message) {
        $critical_patterns = array(
            'database error',
            'connection failed',
            'validation failed',
            'unable to process',
            'critical error'
        );

        foreach ($critical_patterns as $pattern) {
            if (stripos($message, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Notify admin of critical errors
     */
    private function notify_admin($message, $context) {
        $to = get_option('admin_email');
        $subject = sprintf(
            '[%s] Critical E-Invoice Error Detected',
            get_bloginfo('name')
        );
        
        $body = sprintf(
            "A critical error has occurred in the E-Invoice system:\n\n" .
            "Message: %s\n\n" .
            "Context: %s\n\n" .
            "Time: %s\n" .
            "Site: %s",
            $message,
            json_encode($context, JSON_PRETTY_PRINT),
            current_time('mysql'),
            get_site_url()
        );

        wp_mail($to, $subject, $body);
    }

    /**
     * Clean up old log files
     */
    public function cleanup_logs() {
        if (!is_dir(WC_LOG_DIR)) {
            return;
        }

        $files = glob(WC_LOG_DIR . 'einvoice-*.log');
        $max_age = apply_filters('wc_einvoice_log_max_age', 30 * DAY_IN_SECONDS);

        foreach ($files as $file) {
            if (filemtime($file) < time() - $max_age) {
                @unlink($file);
            }
        }
    }

    /**
     * Get all logs for display in admin
     */
    public function get_logs($days = 7) {
        if (!$this->log_enabled || !file_exists($this->log_file)) {
            return array();
        }

        $logs = array();
        $handle = fopen($this->log_file, 'r');

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $logs[] = $this->parse_log_line($line);
            }
            fclose($handle);
        }

        return array_filter($logs);
    }

    /**
     * Parse individual log line
     */
    private function parse_log_line($line) {
        if (preg_match('/\[(.*?)\] (\w+): (.*?)({.*})?$/', $line, $matches)) {
            return array(
                'timestamp' => $matches[1],
                'level' => $matches[2],
                'message' => $matches[3],
                'context' => isset($matches[4]) ? json_decode($matches[4], true) : array()
            );
        }
        return null;
    }
}

// Initialize logger
global $wc_einvoice_logger;
$wc_einvoice_logger = new WC_EInvoice_Logger();

// Helper function to log messages
function wc_einvoice_log($message, $context = array(), $level = 'info') {
    global $wc_einvoice_logger;
    if ($wc_einvoice_logger) {
        $wc_einvoice_logger->log($message, $context, $level);
    }
}
