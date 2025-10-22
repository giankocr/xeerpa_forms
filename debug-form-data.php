<?php
/**
 * Debug script to check form data submission
 * Access: /wp-content/plugins/xpsocial_login/debug-form-data.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied');
}

echo '<h1>XPSocial Form Data Debug</h1>';

// Get recent leads with dynamic fields
global $wpdb;

$leads_table = $wpdb->prefix . 'xpsocial_leads';
$dynamic_fields_table = $wpdb->prefix . 'xpsocial_dynamic_fields';

echo '<h2>Recent Leads (Last 10)</h2>';
$recent_leads = $wpdb->get_results("
    SELECT id, FirstName, LastName, EmailAddress, Source, created_at, dynamic_fields 
    FROM {$leads_table} 
    ORDER BY created_at DESC 
    LIMIT 10
");

if ($recent_leads) {
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Source</th><th>Created</th><th>Dynamic Fields (JSON)</th></tr>';
    foreach ($recent_leads as $lead) {
        echo '<tr>';
        echo '<td>' . $lead->id . '</td>';
        echo '<td>' . $lead->FirstName . ' ' . $lead->LastName . '</td>';
        echo '<td>' . $lead->EmailAddress . '</td>';
        echo '<td>' . $lead->Source . '</td>';
        echo '<td>' . $lead->created_at . '</td>';
        echo '<td>' . ($lead->dynamic_fields ? $lead->dynamic_fields : 'NULL') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p>No leads found.</p>';
}

echo '<h2>Dynamic Fields Table (Last 20)</h2>';
$dynamic_fields = $wpdb->get_results("
    SELECT df.*, l.EmailAddress, l.Source 
    FROM {$dynamic_fields_table} df 
    LEFT JOIN {$leads_table} l ON df.lead_id = l.id 
    ORDER BY df.created_at DESC 
    LIMIT 20
");

if ($dynamic_fields) {
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>ID</th><th>Lead ID</th><th>Email</th><th>Field Name</th><th>Field Value</th><th>Field Type</th><th>Created</th></tr>';
    foreach ($dynamic_fields as $field) {
        echo '<tr>';
        echo '<td>' . $field->id . '</td>';
        echo '<td>' . $field->lead_id . '</td>';
        echo '<td>' . $field->EmailAddress . '</td>';
        echo '<td>' . $field->field_name . '</td>';
        echo '<td>' . htmlspecialchars($field->field_value) . '</td>';
        echo '<td>' . $field->field_type . '</td>';
        echo '<td>' . $field->created_at . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p>No dynamic fields found.</p>';
}

echo '<h2>Form Configuration Check</h2>';
$form_configs = $wpdb->get_results("
    SELECT post_id, meta_key, meta_value 
    FROM {$wpdb->postmeta} 
    WHERE meta_key LIKE '%dynamic_fields%' 
    ORDER BY post_id DESC 
    LIMIT 10
");

if ($form_configs) {
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>Post ID</th><th>Meta Key</th><th>Meta Value</th></tr>';
    foreach ($form_configs as $config) {
        echo '<tr>';
        echo '<td>' . $config->post_id . '</td>';
        echo '<td>' . $config->meta_key . '</td>';
        echo '<td>' . htmlspecialchars(substr($config->meta_value, 0, 200)) . '...</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p>No form configurations found.</p>';
}

echo '<h2>Debug Logs (Last 20 XPSocial entries)</h2>';
$log_file = ABSPATH . 'wp-content/debug.log';
if (file_exists($log_file)) {
    $logs = file($log_file);
    $xpsocial_logs = array_filter($logs, function($line) {
        return strpos($line, 'XPSocial') !== false;
    });
    
    $recent_logs = array_slice($xpsocial_logs, -20);
    
    if ($recent_logs) {
        echo '<pre style="background: #f0f0f0; padding: 10px; max-height: 400px; overflow-y: scroll;">';
        foreach ($recent_logs as $log) {
            echo htmlspecialchars($log);
        }
        echo '</pre>';
    } else {
        echo '<p>No XPSocial logs found.</p>';
    }
} else {
    echo '<p>Debug log file not found.</p>';
}

echo '<h2>Test Form Submission</h2>';
echo '<p>To test form submission, submit a form and check the logs above.</p>';
echo '<p>Look for these specific log entries:</p>';
echo '<ul>';
echo '<li><strong>XPSocial: All POST data:</strong> - Shows all form data</li>';
echo '<li><strong>XPSocial: Dynamic fields data to save:</strong> - Shows processed dynamic fields</li>';
echo '<li><strong>XPSocial: Audio filename from hidden input:</strong> - Shows audio filename</li>';
echo '<li><strong>XPSocial: Guardados X campos dinámicos:</strong> - Shows saved dynamic fields count</li>';
echo '</ul>';

?>
