(function($) {
    'use strict';

    // Tab persistence
    function initTabs() {
        var hash = window.location.hash;
        if (hash) {
            $('.nav-tab-wrapper a[href="' + hash + '"]').trigger('click');
        }

        $('.nav-tab-wrapper a').on('click', function(e) {
            var href = $(this).attr('href');
            if (href && href.indexOf('#') === 0) {
                history.replaceState(null, null, href);
            }
        });
    }

    // Confirm delete actions
    function initConfirmDeletes() {
        $('[data-confirm]').on('click', function(e) {
            if (!confirm($(this).data('confirm'))) {
                e.preventDefault();
            }
        });
    }

    // Slug auto-generation
    function initSlugGeneration() {
        $('#esp-tax-name').on('blur', function() {
            var slugField = $('#esp-tax-slug');
            if (!slugField.val()) {
                slugField.val(
                    $(this).val()
                        .toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .trim()
                );
            }
        });
    }

    // Re-index rows after removal
    function reindexRows(container) {
        container.find('.esp-repeater-row').each(function(index) {
            $(this).data('index', index);
            $(this).find('input, select').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    name = name.replace(/\[\d+\]/g, '[' + index + ']');
                    $(this).attr('name', name);
                }
            });
        });
    }

    $(document).ready(function() {
        initTabs();
        initConfirmDeletes();
        initSlugGeneration();

        // Re-index after remove
        $(document).on('click', '.esp-remove-row', function() {
            var container = $(this).closest('#esp-taxonomies-container, #esp-meta-fields-container');
            setTimeout(function() {
                reindexRows(container);
            }, 50);
        });
    });

})(jQuery);
