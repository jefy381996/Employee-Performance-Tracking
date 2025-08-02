/**
 * Student Result Management - Admin JavaScript
 */

(function($) {
    'use strict';

    // Initialize admin functionality when DOM is ready
    $(document).ready(function() {
        initializeAdmin();
    });

    /**
     * Initialize all admin functionality
     */
    function initializeAdmin() {
        initializeFormValidation();
        initializeTableSorting();
        initializeImageUpload();
        initializeBulkActions();
        initializeCharts();
        initializeNotifications();
        initializeKeyboardShortcuts();
        initializeSearchFilters();
    }

    /**
     * Form validation
     */
    function initializeFormValidation() {
        // Real-time form validation
        $('.srm-student-form, .srm-result-form').on('input', 'input, select', function() {
            validateField($(this));
        });

        // Form submission validation
        $('.srm-student-form, .srm-result-form').on('submit', function(e) {
            if (!validateForm($(this))) {
                e.preventDefault();
                showNotification('Please fix the errors before submitting.', 'error');
            }
        });

        // Roll number availability check
        $('#roll_number').on('blur', function() {
            const rollNumber = $(this).val().trim();
            const studentId = $('input[name="student_id"]').val();
            
            if (rollNumber && rollNumber.length >= 3) {
                checkRollNumberAvailability(rollNumber, studentId);
            }
        });
    }

    /**
     * Validate individual form field
     */
    function validateField($field) {
        const fieldName = $field.attr('name');
        const value = $field.val().trim();
        let isValid = true;
        let message = '';

        // Remove existing error styling
        $field.removeClass('error').next('.error-message').remove();

        // Validation rules
        switch (fieldName) {
            case 'roll_number':
                if (!value) {
                    isValid = false;
                    message = 'Roll number is required.';
                } else if (value.length < 3) {
                    isValid = false;
                    message = 'Roll number must be at least 3 characters.';
                }
                break;

            case 'first_name':
            case 'last_name':
                if (!value) {
                    isValid = false;
                    message = 'This field is required.';
                } else if (value.length < 2) {
                    isValid = false;
                    message = 'Name must be at least 2 characters.';
                }
                break;

            case 'email':
                if (value && !isValidEmail(value)) {
                    isValid = false;
                    message = 'Please enter a valid email address.';
                }
                break;

            case 'phone':
                if (value && !isValidPhone(value)) {
                    isValid = false;
                    message = 'Please enter a valid phone number.';
                }
                break;

            case 'total_marks':
            case 'obtained_marks':
                if (!value || value <= 0) {
                    isValid = false;
                    message = 'Please enter a valid number greater than 0.';
                } else if (fieldName === 'obtained_marks') {
                    const totalMarks = $('input[name="total_marks"]').val();
                    if (totalMarks && parseInt(value) > parseInt(totalMarks)) {
                        isValid = false;
                        message = 'Obtained marks cannot exceed total marks.';
                    }
                }
                break;
        }

        if (!isValid) {
            $field.addClass('error');
            $field.after('<span class="error-message">' + message + '</span>');
        }

        return isValid;
    }

    /**
     * Validate entire form
     */
    function validateForm($form) {
        let isValid = true;
        $form.find('input[required], select[required]').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });
        return isValid;
    }

    /**
     * Check roll number availability
     */
    function checkRollNumberAvailability(rollNumber, studentId) {
        const $field = $('#roll_number');
        const $indicator = $field.next('.availability-indicator');
        
        // Remove existing indicator
        $indicator.remove();
        
        // Add loading indicator
        $field.after('<span class="availability-indicator checking">Checking...</span>');
        
        $.ajax({
            url: srm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'srm_check_roll_number',
                roll_number: rollNumber,
                student_id: studentId || '',
                nonce: srm_ajax.nonce
            },
            success: function(response) {
                $('.availability-indicator').remove();
                if (response.success) {
                    if (response.data.available) {
                        $field.after('<span class="availability-indicator available">✓ Available</span>');
                    } else {
                        $field.after('<span class="availability-indicator taken">✗ Already taken</span>');
                        $field.addClass('error');
                    }
                }
            },
            error: function() {
                $('.availability-indicator').remove();
            }
        });
    }

    /**
     * Table sorting functionality
     */
    function initializeTableSorting() {
        $('.wp-list-table th.sortable').on('click', function() {
            const $th = $(this);
            const column = $th.data('column');
            const currentOrder = $th.hasClass('asc') ? 'desc' : 'asc';
            
            // Update URL with sort parameters
            const url = new URL(window.location);
            url.searchParams.set('orderby', column);
            url.searchParams.set('order', currentOrder);
            window.location.href = url.toString();
        });
    }

    /**
     * Image upload functionality
     */
    function initializeImageUpload() {
        let mediaUploader;

        $('.upload-image-btn').on('click', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $preview = $button.siblings('.image-preview');
            const $input = $button.siblings('input[type="hidden"]');

            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media({
                title: 'Choose Student Photo',
                button: {
                    text: 'Choose Photo'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                
                $input.val(attachment.url);
                $preview.html('<img src="' + attachment.url + '" style="max-width: 150px; height: auto;">');
                $button.text('Change Photo');
                
                // Add remove button
                if (!$preview.siblings('.remove-image-btn').length) {
                    $preview.after('<button type="button" class="button remove-image-btn">Remove Photo</button>');
                }
            });

            mediaUploader.open();
        });

        // Remove image
        $(document).on('click', '.remove-image-btn', function() {
            const $button = $(this);
            const $preview = $button.siblings('.image-preview');
            const $input = $button.siblings('input[type="hidden"]');
            const $uploadBtn = $button.siblings('.upload-image-btn');

            $input.val('');
            $preview.empty();
            $uploadBtn.text('Upload Photo');
            $button.remove();
        });
    }

    /**
     * Bulk actions functionality
     */
    function initializeBulkActions() {
        // Select all checkbox
        $('#cb-select-all-1, #cb-select-all-2').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('.wp-list-table tbody input[type="checkbox"]').prop('checked', isChecked);
            updateBulkActionButtons();
        });

        // Individual checkboxes
        $('.wp-list-table tbody').on('change', 'input[type="checkbox"]', function() {
            updateBulkActionButtons();
        });

        // Bulk action form submission
        $('#doaction, #doaction2').on('click', function(e) {
            const action = $(this).siblings('select').val();
            const checkedItems = $('.wp-list-table tbody input[type="checkbox"]:checked');
            
            if (action === '-1') {
                e.preventDefault();
                showNotification('Please select an action.', 'error');
                return;
            }
            
            if (checkedItems.length === 0) {
                e.preventDefault();
                showNotification('Please select items to perform bulk action.', 'error');
                return;
            }
            
            if (action === 'delete') {
                if (!confirm('Are you sure you want to delete the selected items? This action cannot be undone.')) {
                    e.preventDefault();
                }
            }
        });
    }

    /**
     * Update bulk action button states
     */
    function updateBulkActionButtons() {
        const checkedCount = $('.wp-list-table tbody input[type="checkbox"]:checked').length;
        const $bulkActions = $('.bulkactions select, .bulkactions input[type="submit"]');
        
        if (checkedCount > 0) {
            $bulkActions.prop('disabled', false);
            $('.bulk-action-count').text(checkedCount + ' items selected');
        } else {
            $bulkActions.prop('disabled', true);
            $('.bulk-action-count').text('');
        }
    }

    /**
     * Initialize charts and analytics
     */
    function initializeCharts() {
        // Simple chart implementation for dashboard
        const $chartContainers = $('.srm-chart');
        
        $chartContainers.each(function() {
            const $container = $(this);
            const chartData = $container.data('chart');
            
            if (chartData) {
                renderSimpleChart($container, chartData);
            }
        });
    }

    /**
     * Render simple chart
     */
    function renderSimpleChart($container, data) {
        const width = $container.width();
        const height = 200;
        const maxValue = Math.max(...data.values);
        
        let html = '<div class="simple-chart" style="width: ' + width + 'px; height: ' + height + 'px;">';
        
        data.values.forEach((value, index) => {
            const barHeight = (value / maxValue) * (height - 40);
            const barWidth = (width / data.values.length) - 10;
            const left = index * (barWidth + 10);
            
            html += '<div class="chart-bar" style="left: ' + left + 'px; width: ' + barWidth + 'px; height: ' + barHeight + 'px; bottom: 20px;"></div>';
            html += '<div class="chart-label" style="left: ' + left + 'px; width: ' + barWidth + 'px; bottom: 0;">' + data.labels[index] + '</div>';
        });
        
        html += '</div>';
        $container.html(html);
    }

    /**
     * Notification system
     */
    function initializeNotifications() {
        // Auto-hide notices after 5 seconds
        $('.notice.is-dismissible').delay(5000).fadeOut();
        
        // Manual dismiss
        $(document).on('click', '.notice-dismiss', function() {
            $(this).closest('.notice').fadeOut();
        });
    }

    /**
     * Show custom notification
     */
    function showNotification(message, type = 'info') {
        const $notification = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss</span></button></div>');
        
        $('.wrap h1').after($notification);
        
        setTimeout(() => {
            $notification.fadeOut(() => $notification.remove());
        }, 5000);
    }

    /**
     * Keyboard shortcuts
     */
    function initializeKeyboardShortcuts() {
        $(document).on('keydown', function(e) {
            // Ctrl/Cmd + S to save forms
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const $form = $('.srm-student-form, .srm-result-form, .srm-settings-form').first();
                if ($form.length) {
                    $form.submit();
                }
            }
            
            // Escape to close modals
            if (e.key === 'Escape') {
                $('.srm-modal').fadeOut();
            }
        });
    }

    /**
     * Search and filter functionality
     */
    function initializeSearchFilters() {
        let searchTimeout;
        
        // Real-time search
        $('.srm-search-input').on('input', function() {
            const $input = $(this);
            const query = $input.val().trim();
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 500);
        });
        
        // Filter dropdowns
        $('.srm-filter-select').on('change', function() {
            applyFilters();
        });
    }

    /**
     * Perform search
     */
    function performSearch(query) {
        const currentUrl = new URL(window.location);
        if (query) {
            currentUrl.searchParams.set('s', query);
        } else {
            currentUrl.searchParams.delete('s');
        }
        currentUrl.searchParams.delete('paged'); // Reset pagination
        window.location.href = currentUrl.toString();
    }

    /**
     * Apply filters
     */
    function applyFilters() {
        const currentUrl = new URL(window.location);
        
        $('.srm-filter-select').each(function() {
            const $select = $(this);
            const name = $select.attr('name');
            const value = $select.val();
            
            if (value) {
                currentUrl.searchParams.set(name, value);
            } else {
                currentUrl.searchParams.delete(name);
            }
        });
        
        currentUrl.searchParams.delete('paged'); // Reset pagination
        window.location.href = currentUrl.toString();
    }

    /**
     * Utility functions
     */
    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    function isValidPhone(phone) {
        const regex = /^[\+]?[1-9][\d]{0,15}$/;
        return regex.test(phone.replace(/[\s\-\(\)]/g, ''));
    }

    /**
     * Enhanced subject row management
     */
    $(document).on('click', '#add-subject', function() {
        const $container = $('#subjects-container');
        const currentIndex = $container.find('.subject-row').length;
        
        const html = `
            <div class="subject-row">
                <input type="text" name="subjects[${currentIndex}][name]" placeholder="Subject Name" class="regular-text" required>
                <input type="number" name="subjects[${currentIndex}][total_marks]" placeholder="Total" class="small-text" required>
                <input type="number" name="subjects[${currentIndex}][marks]" placeholder="Obtained" class="small-text" required>
                <input type="text" name="subjects[${currentIndex}][grade]" placeholder="Grade" class="small-text">
                <button type="button" class="button remove-subject">Remove</button>
            </div>
        `;
        
        $container.append(html);
        
        // Animate in
        $container.find('.subject-row:last').hide().slideDown();
    });

    $(document).on('click', '.remove-subject', function() {
        const $row = $(this).closest('.subject-row');
        $row.slideUp(() => $row.remove());
    });

    /**
     * Auto-calculate percentage and grade
     */
    $(document).on('input', 'input[name="total_marks"], input[name="obtained_marks"]', function() {
        const totalMarks = parseInt($('input[name="total_marks"]').val()) || 0;
        const obtainedMarks = parseInt($('input[name="obtained_marks"]').val()) || 0;
        
        if (totalMarks > 0) {
            const percentage = Math.round((obtainedMarks / totalMarks) * 100);
            let grade = '';
            
            if (percentage >= 90) grade = 'A+';
            else if (percentage >= 80) grade = 'A';
            else if (percentage >= 70) grade = 'B+';
            else if (percentage >= 60) grade = 'B';
            else if (percentage >= 50) grade = 'C+';
            else if (percentage >= 40) grade = 'C';
            else grade = 'F';
            
            // Display calculated values
            $('.calculated-percentage').text(percentage + '%');
            $('.calculated-grade').text(grade);
        }
    });

    /**
     * Export functionality
     */
    $(document).on('click', '.export-btn', function() {
        const $btn = $(this);
        const originalText = $btn.text();
        
        $btn.text('Exporting...').prop('disabled', true);
        
        // Restore button after 3 seconds
        setTimeout(() => {
            $btn.text(originalText).prop('disabled', false);
        }, 3000);
    });

})(jQuery);