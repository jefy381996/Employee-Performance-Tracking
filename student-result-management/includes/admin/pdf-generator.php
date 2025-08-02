<?php
/**
 * PDF Generator for Student Result Management System
 * Handles PDF generation for result certificates
 */

if (!defined('ABSPATH')) exit;

class SRM_PDF_Generator {
    
    private $plugin_path;
    private $upload_dir;
    
    public function __construct() {
        $this->plugin_path = SRM_PLUGIN_PATH;
        $this->upload_dir = wp_upload_dir();
    }
    
    /**
     * Generate PDF for student result
     */
    public function generate_result_pdf($student_id, $result_id) {
        global $wpdb;
        
        // Get student data
        $student = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}srm_students WHERE id = %d",
            $student_id
        ));
        
        if (!$student) {
            return array('success' => false, 'message' => 'Student not found');
        }
        
        // Get result data
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}srm_results WHERE id = %d AND student_id = %d",
            $result_id, $student_id
        ));
        
        if (!$result) {
            return array('success' => false, 'message' => 'Result not found');
        }
        
        // Get certificate template
        $certificate_template = $this->get_certificate_template();
        
        // Generate PDF content
        $pdf_content = $this->generate_pdf_content($student, $result, $certificate_template);
        
        // Create PDF file
        $filename = 'result_' . $student->roll_number . '_' . $result_id . '.pdf';
        $file_path = $this->upload_dir['basedir'] . '/srm-certificates/' . $filename;
        
        // Ensure directory exists
        wp_mkdir_p(dirname($file_path));
        
        // Generate PDF using TCPDF or similar
        $pdf_file = $this->create_pdf_file($pdf_content, $file_path);
        
        if ($pdf_file) {
            return array(
                'success' => true,
                'message' => 'PDF generated successfully',
                'download_url' => $this->upload_dir['baseurl'] . '/srm-certificates/' . $filename,
                'file_path' => $file_path
            );
        } else {
            return array('success' => false, 'message' => 'Failed to generate PDF');
        }
    }
    
    /**
     * Get certificate template
     */
    private function get_certificate_template() {
        $template_path = $this->plugin_path . 'assets/certificates/default-template.html';
        
        if (file_exists($template_path)) {
            return file_get_contents($template_path);
        }
        
        // Return default template
        return $this->get_default_template();
    }
    
    /**
     * Get default certificate template
     */
    private function get_default_template() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Result Certificate</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .certificate { border: 3px solid #000; padding: 40px; text-align: center; }
                .header { margin-bottom: 30px; }
                .school-name { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
                .certificate-title { font-size: 20px; margin-bottom: 20px; }
                .student-info { margin: 30px 0; text-align: left; }
                .result-info { margin: 30px 0; }
                .signature { margin-top: 50px; }
                .grade { font-size: 18px; font-weight: bold; }
                .pass { color: green; }
                .fail { color: red; }
            </style>
        </head>
        <body>
            <div class="certificate">
                <div class="header">
                    <div class="school-name">{SCHOOL_NAME}</div>
                    <div class="certificate-title">Result Certificate</div>
                </div>
                
                <div class="student-info">
                    <p><strong>Student Name:</strong> {STUDENT_NAME}</p>
                    <p><strong>Roll Number:</strong> {ROLL_NUMBER}</p>
                    <p><strong>Class:</strong> {CLASS}</p>
                    <p><strong>Exam:</strong> {EXAM_NAME}</p>
                    <p><strong>Date:</strong> {EXAM_DATE}</p>
                </div>
                
                <div class="result-info">
                    <p><strong>Total Marks:</strong> {TOTAL_MARKS}</p>
                    <p><strong>Obtained Marks:</strong> {OBTAINED_MARKS}</p>
                    <p><strong>Percentage:</strong> {PERCENTAGE}%</p>
                    <p><strong>Grade:</strong> <span class="grade">{GRADE}</span></p>
                    <p><strong>Status:</strong> <span class="{STATUS_CLASS}">{STATUS}</span></p>
                </div>
                
                <div class="signature">
                    <p>_____________________</p>
                    <p>Principal\'s Signature</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Generate PDF content with data
     */
    private function generate_pdf_content($student, $result, $template) {
        $school_name = get_option('srm_school_name', get_bloginfo('name'));
        
        $replacements = array(
            '{SCHOOL_NAME}' => $school_name,
            '{STUDENT_NAME}' => $student->first_name . ' ' . $student->last_name,
            '{ROLL_NUMBER}' => $student->roll_number,
            '{CLASS}' => $student->class . ($student->section ? ' - ' . $student->section : ''),
            '{EXAM_NAME}' => $result->exam_name,
            '{EXAM_DATE}' => $result->exam_date,
            '{TOTAL_MARKS}' => $result->total_marks,
            '{OBTAINED_MARKS}' => $result->obtained_marks,
            '{PERCENTAGE}' => $result->percentage,
            '{GRADE}' => $result->grade,
            '{STATUS}' => ucfirst($result->status),
            '{STATUS_CLASS}' => $result->status === 'pass' ? 'pass' : 'fail'
        );
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    /**
     * Create PDF file
     */
    private function create_pdf_file($content, $file_path) {
        // For now, we'll create an HTML file that can be converted to PDF
        // In a production environment, you'd use a proper PDF library like TCPDF or mPDF
        
        $html_content = $content;
        
        // Save as HTML file
        $html_file = str_replace('.pdf', '.html', $file_path);
        $result = file_put_contents($html_file, $html_content);
        
        if ($result) {
            // For demo purposes, we'll create a simple PDF-like file
            // In production, use a proper PDF library
            $pdf_content = $this->create_simple_pdf($content);
            file_put_contents($file_path, $pdf_content);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Create a simple PDF-like file (for demo purposes)
     */
    private function create_simple_pdf($html_content) {
        // This is a simplified version for demo
        // In production, use TCPDF, mPDF, or similar library
        
        $pdf_content = "%PDF-1.4\n";
        $pdf_content .= "1 0 obj\n";
        $pdf_content .= "<<\n";
        $pdf_content .= "/Type /Catalog\n";
        $pdf_content .= "/Pages 2 0 R\n";
        $pdf_content .= ">>\n";
        $pdf_content .= "endobj\n";
        $pdf_content .= "\n";
        $pdf_content .= "2 0 obj\n";
        $pdf_content .= "<<\n";
        $pdf_content .= "/Type /Pages\n";
        $pdf_content .= "/Kids [3 0 R]\n";
        $pdf_content .= "/Count 1\n";
        $pdf_content .= ">>\n";
        $pdf_content .= "endobj\n";
        $pdf_content .= "\n";
        $pdf_content .= "3 0 obj\n";
        $pdf_content .= "<<\n";
        $pdf_content .= "/Type /Page\n";
        $pdf_content .= "/Parent 2 0 R\n";
        $pdf_content .= "/MediaBox [0 0 612 792]\n";
        $pdf_content .= "/Contents 4 0 R\n";
        $pdf_content .= ">>\n";
        $pdf_content .= "endobj\n";
        $pdf_content .= "\n";
        $pdf_content .= "4 0 obj\n";
        $pdf_content .= "<<\n";
        $pdf_content .= "/Length 100\n";
        $pdf_content .= ">>\n";
        $pdf_content .= "stream\n";
        $pdf_content .= "BT\n";
        $pdf_content .= "/F1 12 Tf\n";
        $pdf_content .= "72 720 Td\n";
        $pdf_content .= "(Student Result Certificate) Tj\n";
        $pdf_content .= "ET\n";
        $pdf_content .= "endstream\n";
        $pdf_content .= "endobj\n";
        $pdf_content .= "\n";
        $pdf_content .= "xref\n";
        $pdf_content .= "0 5\n";
        $pdf_content .= "0000000000 65535 f \n";
        $pdf_content .= "0000000009 00000 n \n";
        $pdf_content .= "0000000058 00000 n \n";
        $pdf_content .= "0000000115 00000 n \n";
        $pdf_content .= "0000000214 00000 n \n";
        $pdf_content .= "trailer\n";
        $pdf_content .= "<<\n";
        $pdf_content .= "/Size 5\n";
        $pdf_content .= "/Root 1 0 R\n";
        $pdf_content .= ">>\n";
        $pdf_content .= "startxref\n";
        $pdf_content .= "364\n";
        $pdf_content .= "%%EOF\n";
        
        return $pdf_content;
    }
    
    /**
     * Upload certificate template
     */
    public function upload_certificate_template($file) {
        $upload_dir = $this->plugin_path . 'assets/certificates/';
        wp_mkdir_p($upload_dir);
        
        $filename = 'custom-template.html';
        $file_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            return array('success' => true, 'message' => 'Certificate template uploaded successfully');
        } else {
            return array('success' => false, 'message' => 'Failed to upload certificate template');
        }
    }
    
    /**
     * Get available certificate templates
     */
    public function get_available_templates() {
        $templates_dir = $this->plugin_path . 'assets/certificates/';
        $templates = array();
        
        if (is_dir($templates_dir)) {
            $files = glob($templates_dir . '*.html');
            foreach ($files as $file) {
                $filename = basename($file);
                $templates[] = array(
                    'name' => str_replace('.html', '', $filename),
                    'file' => $filename,
                    'path' => $file
                );
            }
        }
        
        return $templates;
    }
}