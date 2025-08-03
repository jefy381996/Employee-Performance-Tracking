<?php
if (!defined('ABSPATH')) exit;

$style = isset($atts['style']) ? $atts['style'] : 'default';
?>

<div class="srm-result-lookup srm-style-<?php echo esc_attr($style); ?>">
    <div class="srm-lookup-container">
        <div class="srm-lookup-header">
            <h2><?php _e('Student Result Lookup', 'student-result-management'); ?></h2>
            <p><?php _e('Enter your roll number to view your exam results.', 'student-result-management'); ?></p>
        </div>
        
        <div class="srm-lookup-form">
            <form id="srm-result-form">
                <div class="srm-form-group">
                    <label for="srm-roll-number">
                        <?php _e('Roll Number', 'student-result-management'); ?>
                    </label>
                    <div class="srm-input-group">
                        <input type="text" 
                               id="srm-roll-number" 
                               name="roll_number" 
                               placeholder="<?php _e('Enter your roll number', 'student-result-management'); ?>" 
                               required>
                        <button type="submit" class="srm-search-btn">
                            <span class="srm-search-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21 21L16.514 16.506M19 10.5C19 15.194 15.194 19 10.5 19S2 15.194 2 10.5 5.806 2 10.5 2 19 5.806 19 10.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <?php _e('Search Results', 'student-result-management'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="srm-loading" id="srm-loading" style="display: none;">
            <div class="srm-spinner"></div>
            <p><?php _e('Searching for results...', 'student-result-management'); ?></p>
        </div>
        
        <div class="srm-results" id="srm-results" style="display: none;">
            <!-- Results will be populated here via AJAX -->
        </div>
        
        <div class="srm-no-results" id="srm-no-results" style="display: none;">
            <div class="srm-no-results-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.17 21H4.83C3.35 21 2.83 19.77 3.67 18.67L5.84 15.9C6.52 15.03 7.87 15.03 8.55 15.9L10.72 18.67C11.56 19.77 11.04 21 9.56 21H9.17Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 9V13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 17H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3><?php _e('No Results Found', 'student-result-management'); ?></h3>
            <p><?php _e('Sorry, we couldn\'t find any results for this roll number. Please check your roll number and try again.', 'student-result-management'); ?></p>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#srm-result-form').on('submit', function(e) {
        e.preventDefault();
        
        var rollNumber = $('#srm-roll-number').val().trim();
        
        if (!rollNumber) {
            alert('<?php _e('Please enter your roll number.', 'student-result-management'); ?>');
            return;
        }
        
        // Hide previous results
        $('#srm-results, #srm-no-results').hide();
        $('#srm-loading').show();
        
        $.ajax({
            url: srm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'srm_get_result',
                roll_number: rollNumber,
                nonce: srm_ajax.nonce
            },
            success: function(response) {
                $('#srm-loading').hide();
                
                if (response.success) {
                    displayResults(response.data);
                } else {
                    $('#srm-no-results').show();
                }
            },
            error: function() {
                $('#srm-loading').hide();
                alert('<?php _e('An error occurred while searching. Please try again.', 'student-result-management'); ?>');
            }
        });
    });
    
    function displayResults(data) {
        var student = data.student;
        var results = data.results;
        
        var html = '<div class="srm-student-info">';
        html += '<div class="srm-student-header">';
        
        // Student photo (if available and premium)
        if (student.profile_image) {
            html += '<div class="srm-student-photo">';
            html += '<img src="' + student.profile_image + '" alt="' + student.first_name + '">';
            html += '</div>';
        } else {
            html += '<div class="srm-student-photo srm-photo-placeholder">';
            html += '<span>' + student.first_name.charAt(0).toUpperCase() + '</span>';
            html += '</div>';
        }
        
        html += '<div class="srm-student-details">';
        html += '<h3>' + student.first_name + ' ' + student.last_name + '</h3>';
        html += '<div class="srm-student-meta">';
        html += '<span class="srm-roll"><?php _e('Roll Number:', 'student-result-management'); ?> ' + student.roll_number + '</span>';
        html += '<span class="srm-class"><?php _e('Class:', 'student-result-management'); ?> ' + student.class;
        if (student.section) {
            html += ' - ' + student.section;
        }
        html += '</span>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        
        html += '<div class="srm-results-list">';
        html += '<h4><?php _e('Exam Results', 'student-result-management'); ?></h4>';
        
        results.forEach(function(result) {
            html += '<div class="srm-result-card">';
            html += '<div class="srm-result-header">';
            html += '<h5>' + result.exam_name + '</h5>';
            if (result.exam_date) {
                html += '<span class="srm-exam-date">' + formatDate(result.exam_date) + '</span>';
            }
            html += '</div>';
            
            html += '<div class="srm-result-summary">';
            html += '<div class="srm-result-item">';
            html += '<label><?php _e('Total Marks:', 'student-result-management'); ?></label>';
            html += '<span>' + result.total_marks + '</span>';
            html += '</div>';
            html += '<div class="srm-result-item">';
            html += '<label><?php _e('Obtained Marks:', 'student-result-management'); ?></label>';
            html += '<span>' + result.obtained_marks + '</span>';
            html += '</div>';
            html += '<div class="srm-result-item">';
            html += '<label><?php _e('Percentage:', 'student-result-management'); ?></label>';
            html += '<span>' + result.percentage + '%</span>';
            html += '</div>';
            html += '<div class="srm-result-item">';
            html += '<label><?php _e('Grade:', 'student-result-management'); ?></label>';
            html += '<span class="srm-grade grade-' + result.grade.toLowerCase() + '">' + result.grade + '</span>';
            html += '</div>';
            html += '<div class="srm-result-item">';
            html += '<label><?php _e('Status:', 'student-result-management'); ?></label>';
            html += '<span class="srm-status status-' + result.status + '">' + result.status.charAt(0).toUpperCase() + result.status.slice(1) + '</span>';
            html += '</div>';
            html += '</div>';
            
            // Subject-wise marks
            if (result.subjects) {
                try {
                    var subjects = JSON.parse(result.subjects);
                    if (subjects && subjects.length > 0) {
                        html += '<div class="srm-subjects">';
                        html += '<h6><?php _e('Subject-wise Marks', 'student-result-management'); ?></h6>';
                        html += '<div class="srm-subjects-grid">';
                        
                        subjects.forEach(function(subject) {
                            html += '<div class="srm-subject-item">';
                            html += '<span class="srm-subject-name">' + subject.name + '</span>';
                            html += '<span class="srm-subject-marks">' + subject.obtained_marks + '/' + subject.total_marks + '</span>';
                            if (subject.grade) {
                                html += '<span class="srm-subject-grade grade-' + subject.grade.toLowerCase() + '">' + subject.grade + '</span>';
                            }
                            html += '</div>';
                        });
                        
                        html += '</div>';
                        html += '</div>';
                    }
                } catch (e) {
                    // Invalid JSON, skip subjects
                }
            }
            
            html += '</div>';
        });
        
        html += '</div>';
        
        // Action buttons (premium features)
        html += '<div class="srm-result-actions">';
        html += '<button type="button" class="srm-btn srm-btn-secondary srm-print-result">';
        html += '<span class="srm-btn-icon">';
        html += '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
        html += '<path d="M6 9V2H18V9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        html += '<path d="M6 18H4C3.46957 18 2.96086 17.7893 2.58579 17.4142C2.21071 17.0391 2 16.5304 2 16V11C2 10.4696 2.21071 9.96086 2.58579 9.58579C2.96086 9.21071 3.46957 9 4 9H20C20.5304 9 21.0391 9.21071 21.4142 9.58579C21.7893 9.96086 22 10.4696 22 11V16C22 16.5304 21.7893 17.0391 21.4142 17.4142C21.0391 17.7893 20.5304 18 20 18H18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        html += '<path d="M18 14H6V22H18V14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        html += '</svg>';
        html += '</span>';
        html += '<?php _e('Print Result', 'student-result-management'); ?>';
        html += '</button>';
        
        // Check if any result has a certificate PDF
        var hasCertificate = false;
        results.forEach(function(result) {
            if (result.certificate_pdf) {
                hasCertificate = true;
            }
        });
        
        if (hasCertificate) {
            // Find the first result with a certificate
            var resultWithCertificate = results.find(function(result) {
                return result.certificate_pdf;
            });
            
            html += '<button type="button" class="srm-btn srm-btn-primary srm-download-pdf" data-result-id="' + resultWithCertificate.id + '">';
            html += '<span class="srm-btn-icon">';
            html += '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
            html += '<path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
            html += '<path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
            html += '<path d="M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
            html += '</svg>';
            html += '</span>';
            html += '<?php _e('Download Certificate', 'student-result-management'); ?>';
            html += '</button>';
        } else {
            html += '<button type="button" class="srm-btn srm-btn-primary srm-download-pdf" disabled>';
            html += '<span class="srm-btn-icon">';
            html += '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
            html += '<path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
            html += '<path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
            html += '<path d="M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
            html += '</svg>';
            html += '</span>';
            html += '<?php _e('No Certificate Available', 'student-result-management'); ?>';
            html += '</button>';
        }
        html += '</div>';
        
        $('#srm-results').html(html).show();
    }
    
    function formatDate(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString();
    }
    
    // Handle print button
    $(document).on('click', '.srm-print-result', function() {
        window.print();
    });
    
    // Handle PDF download (premium feature)
    $(document).on('click', '.srm-download-pdf', function() {
        var $btn = $(this);
        var resultId = $btn.data('result-id');
        
        if (!resultId) {
            alert('<?php _e('Unable to download certificate. Missing result information.', 'student-result-management'); ?>');
            return;
        }
        
        // Disable button and show loading
        $btn.prop('disabled', true).text('<?php _e('Downloading Certificate...', 'student-result-management'); ?>');
        
        $.ajax({
            url: srm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'srm_download_pdf',
                result_id: resultId,
                nonce: srm_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Create download link
                    var link = document.createElement('a');
                    link.href = response.data.download_url;
                    link.download = 'certificate.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    alert('<?php _e('Certificate downloaded successfully!', 'student-result-management'); ?>');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e('An error occurred while downloading certificate. Please try again.', 'student-result-management'); ?>');
            },
            complete: function() {
                // Re-enable button
                $btn.prop('disabled', false).html('<span class="srm-btn-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span><?php _e('Download Certificate', 'student-result-management'); ?>');
            }
        });
    });
});
</script>