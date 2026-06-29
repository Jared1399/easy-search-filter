(function(wp, components, element, i18n, blockData) {
    var el = element.createElement;
    var __ = i18n.__;
    var SelectControl = components.SelectControl;
    var ToggleControl = components.ToggleControl;
    var registerBlockType = wp.blocks.registerBlockType;

    var groupOptions = [
        { label: __('Select a filter group...', 'easy-search-filter'), value: 0 }
    ];

    if (blockData && blockData.groups) {
        blockData.groups.forEach(function(g) {
            groupOptions.push(g);
        });
    }

    registerBlockType('esp/search-form', {
        title: __('Search Form', 'easy-search-filter'),
        icon: 'search',
        category: 'widgets',
        attributes: {
            groupId: { type: 'number', default: 0 },
            showResults: { type: 'boolean', default: true }
        },
        edit: function(props) {
            var attributes = props.attributes;

            function setGroupId(value) {
                props.setAttributes({ groupId: parseInt(value) });
            }

            function setShowResults(value) {
                props.setAttributes({ showResults: value });
            }

            return el('div', { className: 'esp-block-placeholder' },
                el('div', { style: { padding: '20px', background: '#f0f6fc', borderRadius: '8px', border: '1px solid #c5d9ed' } },
                    el('h3', { style: { margin: '0 0 12px' } }, __('Easy Search & Filter - Form', 'easy-search-filter')),
                    el(SelectControl, {
                        label: __('Filter Group', 'easy-search-filter'),
                        value: attributes.groupId,
                        options: groupOptions,
                        onChange: setGroupId
                    }),
                    el(ToggleControl, {
                        label: __('Show results below form', 'easy-search-filter'),
                        checked: attributes.showResults,
                        onChange: setShowResults
                    })
                )
            );
        },
        save: function() {
            return null;
        }
    });

    registerBlockType('esp/search-results', {
        title: __('Search Results', 'easy-search-filter'),
        icon: 'list-view',
        category: 'widgets',
        attributes: {
            groupId: { type: 'number', default: 0 }
        },
        edit: function(props) {
            var attributes = props.attributes;

            function setGroupId(value) {
                props.setAttributes({ groupId: parseInt(value) });
            }

            return el('div', { className: 'esp-block-placeholder' },
                el('div', { style: { padding: '20px', background: '#f0f6fc', borderRadius: '8px', border: '1px solid #c5d9ed' } },
                    el('h3', { style: { margin: '0 0 12px' } }, __('Easy Search & Filter - Results', 'easy-search-filter')),
                    el(SelectControl, {
                        label: __('Filter Group', 'easy-search-filter'),
                        value: attributes.groupId,
                        options: groupOptions,
                        onChange: setGroupId
                    })
                )
            );
        },
        save: function() {
            return null;
        }
    });

})(window.wp, window.wp.components, window.wp.element, window.wp.i18n, window.espBlockData || {});
