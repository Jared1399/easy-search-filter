(function($) {
    'use strict';

    var indexerTimeout = null;
    var lastChangedField = null;

    // --- Cascading dropdowns ---
    function initCascading() {
        $('.esp-cascading-container').each(function() {
            var container = $(this);
            var field = container.closest('.esp-taxonomy-field');
            var taxonomy = field.data('taxonomy');
            var hiddenInput = field.find('.esp-tax-input');

            container.on('change', 'select.esp-cascade-level', function() {
                var select = $(this);
                var level = parseInt(select.data('level'));
                var selectedSlug = select.val();
                var childrenContainer = select.siblings('.esp-cascade-children');

                lastChangedField = field.data('filter-key');

                childrenContainer.empty();
                hiddenInput.val(selectedSlug);

                if (selectedSlug) {
                    var parent = select.find('option:selected').data('term-id');
                    if (!parent) {
                        select.find('option').each(function() {
                            if ($(this).val() === selectedSlug) parent = $(this).data('term-id');
                        });
                    }
                    if (!parent) return;
                    loadChildTerms(taxonomy, parent, level + 1, childrenContainer, hiddenInput);
                }

                resetFollowingFields(select.closest('.esp-field'));
                triggerIndexerUpdate();
            });
        });
    }

    function loadChildTerms(taxonomy, parentId, level, container, hiddenInput) {
        var loadingText = espData.i18n.loading || 'Loading...';
        container.html('<select class="esp-cascade-level" data-level="' + level + '" disabled><option value="">' + loadingText + '</option></select>');

        $.getJSON(espData.ajaxUrl, {
            action: 'esp_get_child_terms',
            taxonomy: taxonomy,
            parent: parentId
        }, function(response) {
            if (response.success && response.data.length > 0) {
                var html = '<select class="esp-cascade-level" data-level="' + level + '">';
                html += '<option value="" data-esp-count="">' + (espData.i18n.select || 'Select...') + '</option>';
                $.each(response.data, function(i, term) {
                    html += '<option value="' + term.slug + '" data-term-id="' + term.term_id + '" data-esp-count="">' + term.name + '</option>';
                });
                html += '</select>';
                container.html(html);
                triggerIndexerUpdate();
            } else {
                container.empty();
            }
        });
    }

    function maybeRestoreCascading() {
        $('.esp-taxonomy-field').each(function() {
            var field = $(this);
            var hiddenInput = field.find('.esp-tax-input');
            var currentValue = hiddenInput.val();
            if (currentValue) {
                restoreCascadeLevel(field.data('taxonomy'), currentValue, field.find('.esp-cascading-container'), 0, hiddenInput);
            }
        });
    }

    function restoreCascadeLevel(taxonomy, targetSlug, container, level, hiddenInput) {
        var select = container.find('select.esp-cascade-level[data-level="' + level + '"]');
        if (!select.length) return;

        var parentId = level === 0 ? 0 : select.closest('.esp-cascading-container').find('select.esp-cascade-level[data-level="' + (level - 1) + '"] option:selected').data('term-id');
        $.getJSON(espData.ajaxUrl, {
            action: 'esp_get_child_terms',
            taxonomy: taxonomy,
            parent: parentId
        }, function(response) {
            if (!response.success) return;
            select.empty().append('<option value="" data-esp-count="">' + (espData.i18n.select || 'Select...') + '</option>');
            var found = false;
            $.each(response.data, function(i, term) {
                var sel = term.slug === targetSlug ? ' selected' : '';
                if (sel) found = true;
                select.append('<option value="' + term.slug + '" data-term-id="' + term.term_id + '" data-esp-count=""' + sel + '>' + term.name + '</option>');
            });
            if (found) {
                var parent = select.find('option:selected').data('term-id');
                if (parent) {
                    var childrenContainer = $('<div class="esp-cascade-children"></div>');
                    select.after(childrenContainer);
                    loadChildTerms(taxonomy, parent, level + 1, childrenContainer, hiddenInput);
                    var subSelect = childrenContainer.find('select');
                    var interval = setInterval(function() {
                        if (subSelect.find('option').length > 1) {
                            clearInterval(interval);
                            restoreCascadeLevel(taxonomy, targetSlug, container, level + 1, hiddenInput);
                        }
                    }, 100);
                }
            }
        });
    }

    // --- Range Slider ---
    function initRangeSliders() {
        $('.esp-range-slider').each(function() {
            var $slider = $(this);
            var $field = $slider.closest('.esp-range-field');
            var $minInput = $field.find('input[name*="_min"]');
            var $maxInput = $field.find('input[name*="_max"]');
            var $valMin = $field.find('.esp-range-val-min');
            var $valMax = $field.find('.esp-range-val-max');

            var min = parseFloat($slider.data('min'));
            var max = parseFloat($slider.data('max'));
            var startMin = parseFloat($slider.data('start-min'));
            var startMax = parseFloat($slider.data('start-max'));
            var step = parseFloat($slider.data('step')) || 1;

            if (min >= max) return;

            var slider = $slider[0];
            noUiSlider.create(slider, {
                start: [startMin, startMax],
                connect: true,
                step: step,
                range: { 'min': min, 'max': max },
                format: {
                    to: function(v) { return Math.round(v / step) * step; },
                    from: function(v) { return parseFloat(v); }
                }
            });

            slider.noUiSlider.on('update', function(values, handle) {
                var v = Math.round(parseFloat(values[handle]));
                if (handle === 0) {
                    $minInput.val(v);
                    $valMin.text(v);
                } else {
                    $maxInput.val(v);
                    $valMax.text(v);
                }
            });

            slider.noUiSlider.on('end', function() {
                lastChangedField = $field.data('filter-key');
                resetFollowingFields($field);
                triggerIndexerUpdate();
                // Auto-submit if AJAX is enabled
                var form = $slider.closest('.esp-form');
                if (form.length && form.closest('.esp-search-form').data('esp-ajax')) {
                    submitAjaxSearch(form.serializeArray(), form);
                }
            });
        });
    }

    function resetFollowingFields(changedField) {
        var form = changedField.closest('.esp-form');
        if (!form.length) return;

        var startResetting = false;

        form.find('.esp-field').each(function() {
            if ($(this).is(changedField)) {
                startResetting = true;
                return;
            }
            if (!startResetting) return;

            // Reset taxonomy fields: clear hidden inputs and cascade selects
            $(this).find('input.esp-tax-input').val('');
            $(this).find('select.esp-cascade-level').each(function() {
                $(this).prop('selectedIndex', 0);
            });
            $(this).find('.esp-cascade-children').empty();

            // Reset meta fields
            $(this).find('select:not(.esp-cascade-level)').each(function() {
                $(this).prop('selectedIndex', 0);
            });
            $(this).find('input[type="number"]').val('');
            $(this).find('input[type="text"]').val('');
            $(this).find('input[type="checkbox"]').prop('checked', false);

            // Reset range sliders to full range
            $(this).find('.esp-range-slider').each(function() {
                if (this.noUiSlider) {
                    this.noUiSlider.set([$(this).data('min'), $(this).data('max')]);
                    $(this).closest('.esp-range-field').find('input[name*="_min"]').val($(this).data('min'));
                    $(this).closest('.esp-range-field').find('input[name*="_max"]').val($(this).data('max'));
                    $(this).closest('.esp-range-field').find('.esp-range-val-min').text($(this).data('min'));
                    $(this).closest('.esp-range-field').find('.esp-range-val-max').text($(this).data('max'));
                }
            });
        });
    }

    // --- Indexer ---
    function triggerIndexerUpdate() {
        var form = $('.esp-search-form');
        if (form.length === 0 || !form.data('has-indexer')) return;
        if (indexerTimeout) clearTimeout(indexerTimeout);
        indexerTimeout = setTimeout(function() { updateIndexerCounts(form); }, 300);
    }

    function updateIndexerCounts(form) {
        var groupId = form.find('input[name="esp_group_id"]').val();
        if (!groupId) return;

        var formData = form.find('select, input').serializeArray();
        formData.push({ name: 'action', value: 'esp_indexer_counts' });
        formData.push({ name: 'esp_group_id', value: groupId });

        $.post(espData.ajaxUrl, formData, function(response) {
            if (!response.success || !response.data.counts) return;
            var counts = response.data.counts;
            var hideZero = !!form.data('hide-zero');

            var changedFieldEl = null;
            if (lastChangedField) {
                changedFieldEl = form.closest('.esp-search-form').find('[data-filter-key="' + lastChangedField + '"]');
            }

            form.closest('.esp-search-form').find('.esp-taxonomy-field, .esp-meta-field[data-type="select"]').each(function() {
                var key = $(this).data('filter-key');
                if (!key) return;
                var fieldCounts = counts[key] || {};

                // Determine if this field comes AFTER the changed field in DOM order.
                // Only fields AFTER the changed one can hide zero-count options.
                // On initial load (no changed field), ALL fields can hide.
                var shouldHide = true;
                if (changedFieldEl && changedFieldEl.length) {
                    var changedIndex = changedFieldEl.index();
                    var thisIndex = $(this).index();
                    shouldHide = thisIndex > changedIndex;
                    // Never hide the changed field itself
                    if (key === lastChangedField) shouldHide = false;
                }

                $(this).find('option[data-esp-count]').each(function() {
                    var val = $(this).val();
                    var cnt = val && fieldCounts[val] !== undefined ? fieldCounts[val] : 0;
                    $(this).data('esp-count', cnt);
                    var text = $(this).text().replace(/\s*\(\d+\)$/, '');
                    if (hideZero && shouldHide && val && cnt === 0) {
                        $(this).hide();
                    } else {
                        $(this).show();
                        if ($(this).val() && cnt > 0) {
                            $(this).text(text + ' (' + cnt + ')');
                        } else if ($(this).val()) {
                            $(this).text(text);
                        }
                    }
                });
            });
        });
    }

    // --- Sort ---
    function initSort() {
        $(document).on('change', '#esp-sort-select', function() {
            var formWrap = $('.esp-search-form');
            var form = formWrap.find('.esp-form');
            var groupId = formWrap.find('input[name="esp_group_id"]').val();
            var sortVal = $(this).val();

            if (formWrap.data('esp-ajax')) {
                var formData = form.serializeArray();
                formData = formData.filter(function(item) { return item.name !== 'esp_orderby'; });
                if (sortVal) formData.push({ name: 'esp_orderby', value: sortVal });
                submitAjaxSearch(formData, form);
            } else {
                var url = new URL(window.location.href);
                if (sortVal) url.searchParams.set('esp_orderby', sortVal);
                else url.searchParams.delete('esp_orderby');
                url.searchParams.delete('esp_paged');
                window.location.href = url.toString();
            }
        });
    }

    // --- AJAX search ---
    function initAjaxSearch() {
        $('.esp-form').on('submit', function(e) {
            if (!$(this).closest('.esp-search-form').data('esp-ajax')) return;
            e.preventDefault();
            submitAjaxSearch($(this).serializeArray(), $(this));
        });

        $(document).on('click', '.esp-pagination a', function(e) {
            var results = $(this).closest('.esp-results');
            var form = results.siblings('.esp-search-form').find('.esp-form');
            if (!form.length || !form.closest('.esp-search-form').data('esp-ajax')) return;
            e.preventDefault();

            var params = new URLSearchParams($(this).attr('href').split('?')[1] || '');
            var formData = form.serializeArray().filter(function(item) { return item.name !== 'esp_paged'; });
            formData.push({ name: 'esp_paged', value: params.get('esp_paged') || '1' });
            submitAjaxSearch(formData, form);
        });
    }

    function showSkeleton(container, columns) {
        var skeletonHtml = '';
        var count = columns || 3;
        for (var i = 0; i < count; i++) {
            skeletonHtml += '<div class="esp-skeleton-card"><div class="esp-skeleton-img"></div><div class="esp-skeleton-line"></div><div class="esp-skeleton-line esp-skeleton-line-short"></div></div>';
        }
        container.html('<div class="esp-results-grid esp-skeleton-grid" style="--esp-columns:' + count + '">' + skeletonHtml + '</div>');
    }

    function submitAjaxSearch(formData, form) {
        var groupId = form.find('input[name="esp_group_id"]').val();
        if (!groupId) return;

        // Append nonce + action
        var nonceField = form.find('input[name="esp_nonce"]');
        if (nonceField.length) formData.push({ name: 'esp_nonce', value: nonceField.val() });
    formData.push({ name: 'action', value: 'esp_search' });
    formData.push({ name: 'esp_group_id', value: groupId });
    formData.push({ name: 'esp_base_url', value: window.location.origin + window.location.pathname });

        // Sort preservation
        var sortSelect = $('#esp-sort-select');
        if (sortSelect.length && sortSelect.val()) {
            formData = formData.filter(function(item) { return item.name !== 'esp_orderby'; });
            formData.push({ name: 'esp_orderby', value: sortSelect.val() });
        }

        // Find results container
        var resultsContainer = form.closest('.esp-search-form').siblings('.esp-results');
        if (!resultsContainer.length) resultsContainer = $('.esp-results[data-group-id="' + groupId + '"]');

        // Show skeleton
        var columns = resultsContainer.find('.esp-results-grid').css('--esp-columns') || 3;
        showSkeleton(resultsContainer, parseInt(columns));

        $.post(espData.ajaxUrl, formData, function(response) {
            if (response.success) {
                resultsContainer.html(response.data.html);
            } else {
                resultsContainer.html('<p class="esp-no-results">' + (espData.i18n.noResults || 'No results found.') + '</p>');
            }
        });
    }

    // --- Active tags remove via AJAX ---
    function initActiveTagsAjax() {
        $(document).on('click', '.esp-tag', function(e) {
            var form = $('.esp-search-form');
            if (!form.data('esp-ajax')) return;

            var href = $(this).attr('href');
            if ($(this).hasClass('esp-tag-clear')) {
                e.preventDefault();
                // Reset all form fields
                form.find('select').val('');
                form.find('input[type="hidden"]').val('');
                form.find('input[type="number"]').val('');
                form.find('input[type="text"]').val('');
                form.find('input[type="checkbox"]').prop('checked', false);
                submitAjaxSearch(form.serializeArray(), form);
                return;
            }

            // Single tag removal: parse URL params to know which to remove
            e.preventDefault();
            var params = new URLSearchParams(href.split('?')[1] || '');
            var currentParams = new URLSearchParams(window.location.search);

            // Build form data without the removed params
            var formData = [];
            form.find('input, select').each(function() {
                var name = $(this).attr('name');
                if (!name || name === 'esp_paged' || name === 'action' || name === 'esp_nonce' || name === 'esp_group_id') return;
                // If this param is NOT in the new URL, skip it (it was removed)
                if (params.has(name)) {
                    formData.push({ name: name, value: $(this).val() });
                }
            });
            submitAjaxSearch(formData, form);
        });
    }

    // --- Search ---
    function initSearch() {
        var searchTimeout = null;
        $(document).on('input', '.esp-search-input', function() {
            var formWrap = $('.esp-search-form');
            var form = formWrap.find('.esp-form');
            if (searchTimeout) clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                if (formWrap.data('esp-ajax')) {
                    var formData = form.serializeArray();
                    submitAjaxSearch(formData, form);
                }
                triggerIndexerUpdate();
            }, 400);
        });
    }

    // --- Meta field triggers ---
    function initMetaFieldTriggers() {
        $(document).on('change', '.esp-meta-field select, .esp-meta-field input[type="checkbox"]', function() {
            lastChangedField = $(this).closest('.esp-meta-field').data('filter-key');
            resetFollowingFields($(this).closest('.esp-field'));
            triggerIndexerUpdate();
        });
    }

    // --- Init ---
    $(document).ready(function() {
        initCascading();
        maybeRestoreCascading();
        initRangeSliders();
        initSort();
        initMetaFieldTriggers();
        initActiveTagsAjax();
        initSearch();

        if (espData.isPremium) {
            initAjaxSearch();
        }

        setTimeout(function() { triggerIndexerUpdate(); }, 600);
    });

})(jQuery);
