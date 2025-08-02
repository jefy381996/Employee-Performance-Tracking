/**
 * Student Result Management - Frontend JavaScript
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initializeFrontend();
    });

    /**
     * Initialize frontend functionality
     */
    function initializeFrontend() {
        initializeResultLookup();
        initializeAnimations();
        initializeAccessibility();
        initializeKeyboardNavigation();
    }

    /**
     * Result lookup functionality
     */
    function initializeResultLookup() {
        $('#srm-result-form').on('submit', function(e) {
            e.preventDefault();
            performResultLookup();
        });

        // Auto-submit on Enter key
        $('#srm-roll-number').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                performResultLookup();
            }
        });

        // Clear results when input is cleared
        $('#srm-roll-number').on('input', function() {
            if ($(this).val().trim() === '') {
                hideResults();
            }
        });

        // Format roll number input
        $('#srm-roll-number').on('input', function() {
            const value = $(this).val().replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            $(this).val(value);
        });
    }

    /**
     * Perform result lookup
     */
    function performResultLookup() {
        const rollNumber = $('#srm-roll-number').val().trim();

        if (!rollNumber) {
            showError('Please enter your roll number.');
            return;
        }

        if (rollNumber.length < 3) {
            showError('Roll number must be at least 3 characters long.');
            return;
        }

        // Show loading state
        showLoading();

        $.ajax({
            url: srm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'srm_get_result',
                roll_number: rollNumber,
                nonce: srm_ajax.nonce
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    displayResults(response.data);
                    trackResultView(rollNumber);
                } else {
                    showNoResults(response.data || 'No results found for this roll number.');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                showError('Connection error. Please check your internet connection and try again.');
                console.error('AJAX Error:', error);
            }
        });
    }

    /**
     * Display search results
     */
    function displayResults(data) {
        const student = data.student;
        const results = data.results;

        hideNoResults();

        let html = buildStudentInfoHTML(student);
        html += buildResultsListHTML(results);
        html += buildActionButtonsHTML(student, results);

        $('#srm-results').html(html).slideDown(400, function() {
            // Scroll to results
            $('html, body').animate({
                scrollTop: $('#srm-results').offset().top - 20
            }, 500);
        });

        // Initialize result-specific functionality
        initializeResultInteractions();
    }

    /**
     * Build student info HTML
     */
    function buildStudentInfoHTML(student) {
        let html = '<div class="srm-student-info">';
        html += '<div class="srm-student-header">';
        
        // Student photo
        if (student.profile_image) {
            html += '<div class="srm-student-photo">';
            html += '<img src="' + escapeHtml(student.profile_image) + '" alt="' + escapeHtml(student.first_name) + '" loading="lazy">';
            html += '</div>';
        } else {
            html += '<div class="srm-student-photo srm-photo-placeholder">';
            html += '<span>' + escapeHtml(student.first_name.charAt(0).toUpperCase()) + '</span>';
            html += '</div>';
        }
        
        html += '<div class="srm-student-details">';
        html += '<h3>' + escapeHtml(student.first_name + ' ' + student.last_name) + '</h3>';
        html += '<div class="srm-student-meta">';
        html += '<span class="srm-roll">Roll Number: ' + escapeHtml(student.roll_number) + '</span>';
        html += '<span class="srm-class">Class: ' + escapeHtml(student.class);
        if (student.section) {
            html += ' - ' + escapeHtml(student.section);
        }
        html += '</span>';
        if (student.email) {
            html += '<span class="srm-email">Email: ' + escapeHtml(student.email) + '</span>';
        }
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        
        return html;
    }

    /**
     * Build results list HTML
     */
    function buildResultsListHTML(results) {
        let html = '<div class="srm-results-list">';
        html += '<h4>Exam Results (' + results.length + ')</h4>';
        
        results.forEach(function(result, index) {
            html += '<div class="srm-result-card" data-result-id="' + result.id + '">';
            html += buildResultHeaderHTML(result);
            html += buildResultSummaryHTML(result);
            
            if (result.subjects) {
                try {
                    const subjects = JSON.parse(result.subjects);
                    if (subjects && subjects.length > 0) {
                        html += buildSubjectsHTML(subjects);
                    }
                } catch (e) {
                    console.warn('Invalid subjects JSON:', result.subjects);
                }
            }
            
            html += '</div>';
        });
        
        html += '</div>';
        return html;
    }

    /**
     * Build result header HTML
     */
    function buildResultHeaderHTML(result) {
        let html = '<div class="srm-result-header">';
        html += '<h5>' + escapeHtml(result.exam_name) + '</h5>';
        if (result.exam_date) {
            html += '<span class="srm-exam-date">' + formatDate(result.exam_date) + '</span>';
        }
        html += '</div>';
        return html;
    }

    /**
     * Build result summary HTML
     */
    function buildResultSummaryHTML(result) {
        let html = '<div class="srm-result-summary">';
        
        const summaryItems = [
            { label: 'Total Marks', value: result.total_marks },
            { label: 'Obtained Marks', value: result.obtained_marks },
            { label: 'Percentage', value: result.percentage + '%' },
            { label: 'Grade', value: '<span class="srm-grade grade-' + result.grade.toLowerCase() + '">' + result.grade + '</span>' },
            { label: 'Status', value: '<span class="srm-status status-' + result.status + '">' + capitalizeFirst(result.status) + '</span>' }
        ];
        
        summaryItems.forEach(function(item) {
            html += '<div class="srm-result-item">';
            html += '<label>' + item.label + ':</label>';
            html += '<span>' + item.value + '</span>';
            html += '</div>';
        });
        
        html += '</div>';
        return html;
    }

    /**
     * Build subjects HTML
     */
    function buildSubjectsHTML(subjects) {
        let html = '<div class="srm-subjects">';
        html += '<h6>Subject-wise Performance</h6>';
        html += '<div class="srm-subjects-grid">';
        
        subjects.forEach(function(subject) {
            html += '<div class="srm-subject-item">';
            html += '<span class="srm-subject-name">' + escapeHtml(subject.name) + '</span>';
            html += '<span class="srm-subject-marks">' + subject.obtained_marks + '/' + subject.total_marks + '</span>';
            if (subject.grade) {
                html += '<span class="srm-subject-grade grade-' + subject.grade.toLowerCase() + '">' + escapeHtml(subject.grade) + '</span>';
            }
            html += '</div>';
        });
        
        html += '</div>';
        html += '</div>';
        return html;
    }

    /**
     * Build action buttons HTML
     */
    function buildActionButtonsHTML(student, results) {
        let html = '<div class="srm-result-actions">';
        
        // Print button
        html += '<button type="button" class="srm-btn srm-btn-secondary srm-print-result" data-student="' + student.id + '">';
        html += '<span class="srm-btn-icon">';
        html += '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
        html += '<path d="M6 9V2H18V9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        html += '<path d="M6 18H4C3.46957 18 2.96086 17.7893 2.58579 17.4142C2.21071 17.0391 2 16.5304 2 16V11C2 10.4696 2.21071 9.96086 2.58579 9.58579C2.96086 9.21071 3.46957 9 4 9H20C20.5304 9 21.0391 9.21071 21.4142 9.58579C21.7893 9.96086 22 10.4696 22 11V16C22 16.5304 21.7893 17.0391 21.4142 17.4142C21.0391 17.7893 20.5304 18 20 18H18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        html += '<path d="M18 14H6V22H18V14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        html += '</svg>';
        html += '</span>';
        html += 'Print Results';
        html += '</button>';
        
        // PDF download button (premium feature)
        html += '<button type="button" class="srm-btn srm-btn-primary srm-download-pdf" disabled>';
        html += '<span class="srm-btn-icon">';
        html += '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
        html += '<path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        html += '<path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        html += '<path d="M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        html += '</svg>';
        html += '</span>';
        html += 'Download PDF <span class="srm-premium-badge">Premium</span>';
        html += '</button>';
        
        html += '</div>';
        return html;
    }

    /**
     * Initialize result interactions
     */
    function initializeResultInteractions() {
        // Print functionality
        $('.srm-print-result').off('click').on('click', function() {
            printResults();
        });
        
        // PDF download (premium feature)
        $('.srm-download-pdf').off('click').on('click', function() {
            showPremiumNotice();
        });
        
        // Result card hover effects
        $('.srm-result-card').on('mouseenter', function() {
            $(this).addClass('hover');
        }).on('mouseleave', function() {
            $(this).removeClass('hover');
        });
    }

    /**
     * Print results
     */
    function printResults() {
        // Hide form and show only results for printing
        $('.srm-lookup-form, .srm-result-actions').hide();
        
        window.print();
        
        // Restore visibility after print
        setTimeout(function() {
            $('.srm-lookup-form, .srm-result-actions').show();
        }, 1000);
    }

    /**
     * Show premium feature notice
     */
    function showPremiumNotice() {
        const message = 'PDF download is a premium feature. Please contact your school administrator for access.';
        showError(message);
    }

    /**
     * Show loading state
     */
    function showLoading() {
        hideResults();
        hideNoResults();
        $('#srm-loading').slideDown(300);
    }

    /**
     * Hide loading state
     */
    function hideLoading() {
        $('#srm-loading').slideUp(300);
    }

    /**
     * Show no results message
     */
    function showNoResults(message) {
        hideResults();
        hideLoading();
        
        if (message && message !== 'No results found for this roll number.') {
            $('#srm-no-results p').text(message);
        }
        
        $('#srm-no-results').slideDown(400);
    }

    /**
     * Hide no results message
     */
    function hideNoResults() {
        $('#srm-no-results').slideUp(300);
    }

    /**
     * Hide results
     */
    function hideResults() {
        $('#srm-results').slideUp(300);
    }

    /**
     * Show error message
     */
    function showError(message) {
        // Create or update error notification
        let $error = $('.srm-error-notification');
        
        if ($error.length === 0) {
            $error = $('<div class="srm-error-notification"></div>');
            $('.srm-lookup-form').after($error);
        }
        
        $error.html('<p>' + message + '</p>')
              .removeClass('hidden')
              .slideDown(300);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $error.slideUp(300);
        }, 5000);
    }

    /**
     * Initialize animations
     */
    function initializeAnimations() {
        // Add entrance animations to elements
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, observerOptions);
        
        // Observe elements for animation
        $('.srm-lookup-container').each(function() {
            observer.observe(this);
        });
    }

    /**
     * Initialize accessibility features
     */
    function initializeAccessibility() {
        // Add ARIA labels
        $('#srm-roll-number').attr('aria-describedby', 'roll-number-help');
        
        // Add help text
        if ($('#roll-number-help').length === 0) {
            $('#srm-roll-number').after('<div id="roll-number-help" class="screen-reader-text">Enter your student roll number to search for results</div>');
        }
        
        // Enhanced focus management
        $('#srm-result-form').on('submit', function() {
            $('#srm-loading').attr('aria-live', 'polite');
        });
        
        // Announce results to screen readers
        $(document).on('srm:results-loaded', function() {
            const resultCount = $('.srm-result-card').length;
            const announcement = resultCount + ' exam result' + (resultCount !== 1 ? 's' : '') + ' found';
            
            let $announcer = $('#srm-announcer');
            if ($announcer.length === 0) {
                $announcer = $('<div id="srm-announcer" class="screen-reader-text" aria-live="polite"></div>');
                $('body').append($announcer);
            }
            
            $announcer.text(announcement);
        });
    }

    /**
     * Initialize keyboard navigation
     */
    function initializeKeyboardNavigation() {
        // Escape key to clear search
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('#srm-roll-number').val('').focus();
                hideResults();
                hideNoResults();
            }
        });
        
        // Tab navigation enhancement
        $('.srm-result-card').attr('tabindex', '0');
        
        // Keyboard navigation for result cards
        $('.srm-result-card').on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });
    }

    /**
     * Track result view for analytics
     */
    function trackResultView(rollNumber) {
        // Simple analytics tracking
        if (typeof gtag !== 'undefined') {
            gtag('event', 'result_view', {
                'event_category': 'Student Results',
                'event_label': rollNumber,
                'value': 1
            });
        }
        
        // Trigger custom event
        $(document).trigger('srm:results-loaded', { rollNumber: rollNumber });
    }

    /**
     * Utility functions
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /**
     * Advanced search features
     */
    function initializeAdvancedSearch() {
        // Search suggestions
        const recentSearches = JSON.parse(localStorage.getItem('srm_recent_searches') || '[]');
        
        if (recentSearches.length > 0) {
            let suggestionsHtml = '<div class="srm-search-suggestions">';
            suggestionsHtml += '<p>Recent searches:</p>';
            recentSearches.slice(0, 5).forEach(function(search) {
                suggestionsHtml += '<button type="button" class="srm-suggestion" data-value="' + escapeHtml(search) + '">' + escapeHtml(search) + '</button>';
            });
            suggestionsHtml += '</div>';
            
            $('.srm-lookup-form').append(suggestionsHtml);
        }
        
        // Handle suggestion clicks
        $(document).on('click', '.srm-suggestion', function() {
            const value = $(this).data('value');
            $('#srm-roll-number').val(value);
            $('.srm-search-suggestions').hide();
            performResultLookup();
        });
        
        // Save search to recent searches
        $(document).on('srm:search-performed', function(e, rollNumber) {
            let searches = JSON.parse(localStorage.getItem('srm_recent_searches') || '[]');
            searches = searches.filter(s => s !== rollNumber);
            searches.unshift(rollNumber);
            searches = searches.slice(0, 10); // Keep only last 10
            localStorage.setItem('srm_recent_searches', JSON.stringify(searches));
        });
    }

    // Initialize advanced features
    initializeAdvancedSearch();

})(jQuery);