(function (wp) {
  'use strict';
  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var PanelBody = wp.components.PanelBody;
  var TextControl = wp.components.TextControl;
  var RangeControl = wp.components.RangeControl;
  var SelectControl = wp.components.SelectControl;
  var t = function (text) { return wp.i18n.__(text, 'cluuub-embed'); };
  registerBlockType('cluuub/embed', {
    edit: function (props) {
      var a = props.attributes;
      function field(key, value) { props.setAttributes((function () { var x = {}; x[key] = value; return x; }())); }
      return el('div', { className: props.className },
        el(InspectorControls, {}, el(PanelBody, { title: t('cluuub Embed'), initialOpen: true },
          el(TextControl, { label: t('Club slug'), value: a.club, onChange: function (v) { field('club', v); }, help: t('Use the public club slug from cluuub.') }),
          el(SelectControl, { label: t('Content'), value: a.type, options: [
            { label: t('Sport offers'), value: 'angebote' }, { label: t('Trainers'), value: 'trainer' },
            { label: t('Venues'), value: 'hallen' }, { label: t('Board contacts'), value: 'vorstand' },
            { label: t('Sponsors'), value: 'sponsoren' }
          ], onChange: function (v) { field('type', v); } }),
          el(RangeControl, { label: t('Maximum items'), min: 1, max: 50, value: a.limit, onChange: function (v) { field('limit', v); } }),
          el(TextControl, { label: t('Search filter (optional)'), value: a.filter, onChange: function (v) { field('filter', v); } })
        )),
        el('div', { style: { padding: '1rem', border: '1px solid #ccd0d4', textAlign: 'center' } },
          el('strong', {}, t('cluuub Embed')),
          el('p', {}, a.club ? t('Club: ') + a.club : t('Enter the club slug in the block settings.')),
          el('small', {}, t('Selected public data is fetched through WordPress and briefly cached.'))));
    },
    save: function () { return null; }
  });
}(window.wp));
