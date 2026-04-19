/* global wp */
(function (blocks, element, blockEditor, components) {
	'use strict';

	var el               = element.createElement;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody        = components.PanelBody;
	var TextControl      = components.TextControl;
	var RangeControl     = components.RangeControl;

	blocks.registerBlockType('ai-wish/generator', {
		title:       'AI Generator Życzeń',
		description: 'Inteligentny generator życzeń zasilany przez Gemini AI.',
		category:    'widgets',
		icon:        'format-quote',
		supports: {
			html:      false,
			multiple:  true,
			reusable:  true,
		},

		attributes: {
			occasions: { type: 'string',  default: '' },
			tones:     { type: 'string',  default: '' },
			variants:  { type: 'integer', default: 3  },
		},

		edit: function (props) {
			var attrs = props.attributes;

			var inspectorControls = el(
				InspectorControls,
				{ key: 'inspector' },
				el(
					PanelBody,
					{ title: 'Ustawienia generatora', initialOpen: true },

					el(RangeControl, {
						label:    'Liczba wariantów (1–5)',
						value:    attrs.variants,
						min:      1,
						max:      5,
						onChange: function (v) { props.setAttributes({ variants: v }); },
					}),

					el(TextControl, {
						label:    'Okazje (opcjonalnie)',
						value:    attrs.occasions,
						onChange: function (v) { props.setAttributes({ occasions: v }); },
						help:     'Klucze oddzielone przecinkiem, np: urodziny,slub,komunia — puste = wszystkie.',
					}),

					el(TextControl, {
						label:    'Tony (opcjonalnie)',
						value:    attrs.tones,
						onChange: function (v) { props.setAttributes({ tones: v }); },
						help:     'Klucze oddzielone przecinkiem, np: wzruszajacy,smieszny — puste = wszystkie.',
					})
				)
			);

			var infoLine = 'Warianty: ' + attrs.variants;
			if (attrs.occasions) infoLine += ' · Okazje: ' + attrs.occasions;
			if (attrs.tones)     infoLine += ' · Tony: '   + attrs.tones;

			var preview = el(
				'div',
				{ key: 'preview', className: 'bwg-editor-preview' },
				el('div', { className: 'bwg-editor-preview__icon' }, '✨'),
				el('p',   { className: 'bwg-editor-preview__title' }, 'AI Generator Życzeń'),
				el('p',   { className: 'bwg-editor-preview__info'  }, infoLine),
				el('p',   { className: 'bwg-editor-preview__hint'  }, 'Generator wyrenderuje się na froncie strony.')
			);

			return [ inspectorControls, preview ];
		},

		save: function () {
			return null; // Dynamic block — rendered server-side by bwg_block_render()
		},
	});

})(wp.blocks, wp.element, wp.blockEditor, wp.components);
