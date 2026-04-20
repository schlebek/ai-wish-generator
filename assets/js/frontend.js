/* global jQuery, BWG */
/* eslint-disable no-console */
(function ($) {
	'use strict';

	/* ============================================================
	   Config
	   ============================================================ */

	var CARD_TEMPLATES = [
		{
			id: 'klasyczny', label: '🌸 Klasyczny',
			cardStyle: 'background:#fffaf0;border:2px dashed #ff6b6b;border-radius:16px;',
			textStyle: 'color:#333;font-family:Georgia,serif;font-size:16px;line-height:1.8;',
		},
		{
			id: 'kwiatowy', label: '🌿 Kwiatowy',
			cardStyle: 'background:linear-gradient(135deg,#f0fdf4,#d1fae5);border:2px solid #6ee7b7;border-radius:16px;',
			textStyle: 'color:#065f46;font-family:Georgia,serif;font-size:16px;line-height:1.8;',
		},
		{
			id: 'niebieski', label: '💙 Błękit',
			cardStyle: 'background:linear-gradient(135deg,#eff6ff,#dbeafe);border:2px solid #93c5fd;border-radius:16px;',
			textStyle: 'color:#1e40af;font-family:Georgia,serif;font-size:16px;line-height:1.8;',
		},
		{
			id: 'zloty', label: '✨ Złoty',
			cardStyle: 'background:linear-gradient(135deg,#fefce8,#fef3c7);border:2px solid #fcd34d;border-radius:16px;',
			textStyle: 'color:#78350f;font-family:Georgia,serif;font-size:16px;line-height:1.8;',
		},
		{
			id: 'minimalny', label: '⬜ Minimalistyczny',
			cardStyle: 'background:#fff;border:1px solid #d1d5db;border-radius:6px;',
			textStyle: 'color:#111827;font-family:Arial,sans-serif;font-size:15px;line-height:1.8;',
		},
	];

	var selectedTemplate = CARD_TEMPLATES[0];
	var activeCardText   = '';
	var activeCardIdx    = 0;
	var exportFilename   = 'zyczenia';

	/* ============================================================
	   State / locks
	   ============================================================ */

	var isGenerating = false;

	/* ============================================================
	   Helpers
	   ============================================================ */

	function escHtml(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/\n/g, '<br>');
	}

	function showToast(msg, duration) {
		duration = duration || 2200;
		var toast = document.getElementById('bwg-toast');
		if (!toast) {
			toast = document.createElement('div');
			toast.id = 'bwg-toast';
			toast.className = 'bwg-toast';
			document.body.appendChild(toast);
		}
		toast.textContent = msg;
		toast.classList.add('bwg-toast--visible');
		clearTimeout(toast._timer);
		toast._timer = setTimeout(function () {
			toast.classList.remove('bwg-toast--visible');
		}, duration);
	}

	function statsText(text) {
		var clean = (text || '').replace(/<br\s*\/?>/gi, ' ');
		var words = clean.trim().split(/\s+/).filter(Boolean).length;
		var chars = clean.replace(/\s/g, '').length;
		return words + ' ' + BWG.i18n.words + ' · ' + chars + ' ' + BWG.i18n.chars;
	}

	function copyText(text) {
		navigator.clipboard.writeText(text).then(function () {
			showToast(BWG.i18n.copied + ' ❤️');
		}).catch(function () {
			showToast('Błąd kopiowania — spróbuj ręcznie.');
		});
	}

	/* ============================================================
	   Card preview modal
	   ============================================================ */

	function buildTemplatePicker() {
		var picker = document.getElementById('bwg-template-picker');
		if (!picker) return;
		picker.innerHTML = '';
		CARD_TEMPLATES.forEach(function (tpl) {
			var btn = document.createElement('button');
			btn.type      = 'button';
			btn.className = 'bwg-tpl-btn' + (tpl.id === selectedTemplate.id ? ' bwg-tpl-btn--active' : '');
			btn.dataset.tpl = tpl.id;
			btn.style.cssText = tpl.cardStyle + 'min-width:70px;padding:6px 10px;font-size:12px;cursor:pointer;font-weight:600;border:none;outline:2px solid transparent;';
			btn.textContent = tpl.label;
			picker.appendChild(btn);
		});
	}

	function applyTemplateToPreview(tpl) {
		var card = document.getElementById('bwg-preview-card');
		var text = document.getElementById('bwg-preview-text');
		if (!card || !text) return;
		card.setAttribute('style', tpl.cardStyle + 'padding:28px;max-width:480px;width:100%;box-sizing:border-box;');
		text.setAttribute('style', tpl.textStyle + 'margin:0;white-space:pre-line;');
	}

	function openModal(wishText, filename) {
		activeCardText = wishText;
		exportFilename = filename || 'zyczenia';

		var modal = document.getElementById('bwg-modal');
		if (!modal) return;

		var previewText = document.getElementById('bwg-preview-text');
		if (previewText) previewText.textContent = wishText;

		buildTemplatePicker();
		applyTemplateToPreview(selectedTemplate);

		modal.style.display = 'flex';
		document.body.style.overflow = 'hidden';
	}

	function closeModal() {
		var modal = document.getElementById('bwg-modal');
		if (modal) modal.style.display = 'none';
		document.body.style.overflow = '';
	}

	/* ============================================================
	   Generate wishes
	   ============================================================ */

	function generateWishes(isRegen) {
		if (isGenerating) return;

		var btn       = document.getElementById('bwg-submit-btn');
		var loader    = document.getElementById('bwg-loader');
		var loaderTxt = document.getElementById('bwg-loader-text');
		var results   = document.getElementById('bwg-results');
		var remaining = document.getElementById('bwg-remaining');

		var sender    = (document.getElementById('bwg_sender')    || {}).value || '';
		var recipient = (document.getElementById('bwg_recipient') || {}).value || '';
		var age       = (document.getElementById('bwg_age')       || {}).value || '';
		var occasion  = (document.getElementById('bwg_occasion')  || {}).value || '';
		var tone      = (document.getElementById('bwg_tone')      || {}).value || '';
		var length    = (document.getElementById('bwg_length')    || {}).value || 'standardowe';
		var rhyme     = (document.getElementById('bwg_rhyme')     || {}).checked ? '1' : '0';

		sender    = sender.trim();
		recipient = recipient.trim();

		if (!sender || !recipient) {
			showToast(BWG.i18n.fill_fields);
			return;
		}

		isGenerating = true;
		btn.disabled    = true;
		btn.textContent = BWG.i18n.generating;
		loader.style.display  = 'flex';
		remaining.style.display = 'none';
		if (!isRegen) results.innerHTML = '';

		var genMessages = [
			'Generujemy dla Ciebie piękne słowa…',
			'Dobieramy właściwy ton i styl…',
			'Szlifujemy każde zdanie…',
			'Nadajemy wyjątkowy charakter…',
			'Już prawie gotowe…',
		];
		var genMsgIdx = 0;
		if (loaderTxt) loaderTxt.textContent = genMessages[0];
		var genMsgTimer = setInterval(function () {
			genMsgIdx = (genMsgIdx + 1) % genMessages.length;
			if (loaderTxt) loaderTxt.textContent = genMessages[genMsgIdx];
		}, 2600);

		$.ajax({
			url:  BWG.ajax_url,
			type: 'POST',
			data: {
				action:    'bwg_generate',
				nonce:     BWG.nonce,
				sender:    sender,
				recipient: recipient,
				age:       age,
				occasion:  occasion,
				tone:      tone,
				length:    length,
				rhyme:     rhyme,
				variants:  BWG.variants,
				force:     isRegen ? '1' : '0',
			},
			success: function (response) {
				clearInterval(genMsgTimer);
				isGenerating    = false;
				loader.style.display = 'none';
				btn.disabled    = false;
				btn.textContent = BWG.i18n.generate_btn;

				if (!response.success) {
					results.innerHTML = '<div class="bwg-error">' + escHtml((response.data || {}).message || BWG.i18n.error) + '</div>';
					return;
				}

				renderWishes(results, response.data.wishes, sender, recipient);

				var rem = response.data.remaining;
				if (rem !== undefined && rem !== 999) {
					remaining.textContent   = 'Pozostałe generowania dziś: ' + rem;
					remaining.style.display = 'block';
				}
			},
			error: function () {
				clearInterval(genMsgTimer);
				isGenerating    = false;
				loader.style.display = 'none';
				btn.disabled    = false;
				btn.textContent = BWG.i18n.generate_btn;
				results.innerHTML = '<div class="bwg-error">' + escHtml(BWG.i18n.error) + '</div>';
			},
		});
	}

	/* ============================================================
	   Render result cards
	   ============================================================ */

	function renderWishes(container, wishes, sender, recipient) {
		container.innerHTML = '';

		wishes.forEach(function (text, idx) {
			var card = document.createElement('div');
			card.className = 'bwg-card';

			var badge = wishes.length > 1
				? '<span class="bwg-card__badge">Wariant ' + (idx + 1) + '</span>'
				: '';

			card.innerHTML =
				badge +
				'<p class="bwg-card__text" id="bwg-text-' + idx + '"' +
					' contenteditable="true"' +
					' title="' + escHtml(BWG.i18n.edit_hint) + '"' +
					' data-orig="' + escHtml(text) + '"' +
				'>' + escHtml(text) + '</p>' +
				'<div class="bwg-card__counter" id="bwg-counter-' + idx + '">' + statsText(text) + '</div>' +
				'<div class="bwg-card__actions">' +
					'<button class="bwg-btn bwg-btn--ghost" data-action="copy"    data-idx="' + idx + '">📋 Kopiuj</button>' +
					'<button class="bwg-btn bwg-btn--ghost" data-action="preview" data-idx="' + idx + '">🎨 Pobierz kartę</button>' +
					'<button class="bwg-btn bwg-btn--ghost" data-action="share-fb" data-idx="' + idx + '">📘 Facebook</button>' +
					'<button class="bwg-btn bwg-btn--ghost" data-action="share-wa" data-idx="' + idx + '">💬 WhatsApp</button>' +
					'<button class="bwg-btn bwg-btn--ghost" data-action="improve-use" data-idx="' + idx + '">✨ Ulepsz</button>' +
				'</div>';

			container.appendChild(card);

			// Live counter update on edit
			var textEl = card.querySelector('#bwg-text-' + idx);
			var counter = card.querySelector('#bwg-counter-' + idx);
			if (textEl && counter) {
				textEl.addEventListener('input', function () {
					counter.textContent = statsText(this.innerText);
				});
			}
		});

		var regenRow = document.createElement('div');
		regenRow.className = 'bwg-regen-row';
		regenRow.innerHTML = '<button class="bwg-btn bwg-btn--secondary bwg-regen" id="bwg-regen-btn">🔄 Generuj ponownie</button>';
		container.appendChild(regenRow);

		container.addEventListener('click', handleCardAction);
		document.getElementById('bwg-regen-btn').addEventListener('click', function () {
			generateWishes(true);
		});
	}

	function handleCardAction(e) {
		var btn = e.target.closest('[data-action]');
		if (!btn) return;

		var action = btn.dataset.action;
		var idx    = parseInt(btn.dataset.idx, 10);
		var textEl = document.getElementById('bwg-text-' + idx);
		var text   = textEl ? textEl.innerText : '';

		if (action === 'copy') {
			copyText(text);
			return;
		}

		if (action === 'preview') {
			openModal(text, 'zyczenia-' + (idx + 1));
			return;
		}

		if (action === 'share-fb') {
			var fbUrl = 'https://www.facebook.com/sharer/sharer.php?u=' +
				encodeURIComponent(window.location.href) +
				'&quote=' + encodeURIComponent(text);
			window.open(fbUrl, '_blank', 'width=600,height=400');
			return;
		}

		if (action === 'share-wa') {
			var waUrl = 'https://wa.me/?text=' + encodeURIComponent(text);
			window.open(waUrl, '_blank');
			return;
		}

		if (action === 'improve-use') {
			var textarea = document.getElementById('bwg_improve_text');
			var body     = document.getElementById('bwg-improve-body');
			if (textarea) {
				textarea.value = text;
				if (body && body.style.display === 'none') {
					body.style.display = 'block';
					var toggle = document.getElementById('bwg-improve-toggle');
					if (toggle) toggle.setAttribute('aria-expanded', 'true');
					var arrow = toggle && toggle.querySelector('.bwg-accordion-arrow');
					if (arrow) arrow.textContent = '▴';
				}
				textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
				showToast('Tekst wklejony do sekcji ulepszania.');
			}
		}
	}

	/* ============================================================
	   Zaskocz mnie (randomize)
	   ============================================================ */

	function surprise() {
		if (isGenerating) return;

		var sender    = (document.getElementById('bwg_sender')    || {}).value || '';
		var recipient = (document.getElementById('bwg_recipient') || {}).value || '';
		if (!sender.trim() || !recipient.trim()) {
			showToast(BWG.i18n.fill_fields);
			return;
		}

		function pickRandom(arr) {
			return arr[Math.floor(Math.random() * arr.length)];
		}

		var occEl = document.getElementById('bwg_occasion');
		var tonEl = document.getElementById('bwg_tone');
		var lenEl = document.getElementById('bwg_length');

		if (occEl) {
			var newOcc = pickRandom(BWG.occasions_keys);
			occEl.value = newOcc;
			occEl.classList.add('bwg-highlight');
			setTimeout(function () { occEl.classList.remove('bwg-highlight'); }, 1200);
		}

		if (tonEl) {
			var newTon = pickRandom(BWG.tones_keys);
			tonEl.value = newTon;
			tonEl.classList.add('bwg-highlight');
			setTimeout(function () { tonEl.classList.remove('bwg-highlight'); }, 1200);
		}

		if (lenEl) {
			var newLen = pickRandom(BWG.lengths_keys);
			lenEl.value = newLen;
			updateRhymeToggle(newLen);
		}

		var occLabel = occEl ? occEl.options[occEl.selectedIndex].text : '';
		var tonLabel = tonEl ? tonEl.options[tonEl.selectedIndex].text : '';
		var lenLabel = lenEl ? lenEl.options[lenEl.selectedIndex].text : '';
		showToast('🎲 Wylosowano: ' + occLabel + ' · ' + tonLabel + ' · ' + lenLabel, 4000);

		setTimeout(function () { generateWishes(false); }, 500);
	}

	/* ============================================================
	   Rhyme toggle / length sync
	   ============================================================ */

	function updateRhymeToggle(lengthVal) {
		var rhymeToggle = document.getElementById('bwg_rhyme');
		if (!rhymeToggle) return;
		if (lengthVal === 'wiersz') {
			rhymeToggle.checked  = true;
			rhymeToggle.disabled = true;
		} else {
			rhymeToggle.disabled = false;
		}
	}

	/* ============================================================
	   Improve section
	   ============================================================ */

	function improveWish() {
		var btn       = document.getElementById('bwg-improve-btn');
		var textarea  = document.getElementById('bwg_improve_text');
		var direction = (document.getElementById('bwg_improve_direction') || {}).value || 'ogolne';
		var resultDiv = document.getElementById('bwg-improve-result');
		var textOut   = document.getElementById('bwg-improve-text-out');
		var counter   = document.getElementById('bwg-improve-counter');
		var loader    = document.getElementById('bwg-loader');
		var loaderTxt = document.getElementById('bwg-loader-text');

		if (!textarea || textarea.value.trim().length < 10) {
			showToast('Wpisz tekst życzeń do ulepszenia.');
			return;
		}

		if (isGenerating) return;
		isGenerating = true;

		btn.disabled    = true;
		btn.textContent = BWG.i18n.improving;
		if (loader) loader.style.display = 'flex';
		if (resultDiv) resultDiv.style.display = 'none';

		var impMessages = [
			'Ciężko pracujemy nad Twoimi życzeniami…',
			'Analizujemy styl i emocje…',
			'Nadajemy nowego blasku słowom…',
			'Już prawie gotowe…',
		];
		var impMsgIdx = 0;
		if (loaderTxt) loaderTxt.textContent = impMessages[0];
		var impMsgTimer = setInterval(function () {
			impMsgIdx = (impMsgIdx + 1) % impMessages.length;
			if (loaderTxt) loaderTxt.textContent = impMessages[impMsgIdx];
		}, 2600);

		$.ajax({
			url:  BWG.ajax_url,
			type: 'POST',
			data: {
				action:    'bwg_improve',
				nonce:     BWG.nonce,
				text:      textarea.value,
				direction: direction,
			},
			success: function (response) {
				clearInterval(impMsgTimer);
				isGenerating    = false;
				btn.disabled    = false;
				btn.textContent = BWG.i18n.improve_btn;
				if (loader) loader.style.display = 'none';

				if (!response.success) {
					showToast((response.data || {}).message || BWG.i18n.error, 3000);
					return;
				}

				var improved = response.data.improved || '';
				if (textOut) {
					textOut.innerText = improved;
					textOut.addEventListener('input', function () {
						if (counter) counter.textContent = statsText(this.innerText);
					});
				}
				if (counter)   counter.textContent = statsText(improved);
				if (resultDiv) resultDiv.style.display = 'block';
				resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
			},
			error: function () {
				clearInterval(impMsgTimer);
				isGenerating    = false;
				btn.disabled    = false;
				btn.textContent = BWG.i18n.improve_btn;
				if (loader) loader.style.display = 'none';
				showToast(BWG.i18n.error, 3000);
			},
		});
	}

	/* ============================================================
	   Export (lazy load + html2canvas)
	   ============================================================ */

	function exportCard(format) {
		var btn = format === 'jpg'
			? document.getElementById('bwg-modal-jpg')
			: document.getElementById('bwg-modal-pdf');

		if (btn) { btn.disabled = true; btn.textContent = '⏳ Generowanie…'; }

		/* global html2canvas, jspdf */
		var card = document.getElementById('bwg-preview-card');
		html2canvas(card, { scale: 2, backgroundColor: null, useCORS: true }).then(function (canvas) {
			if (format === 'jpg') {
				var link = document.createElement('a');
				link.download = exportFilename + '.jpg';
				link.href = canvas.toDataURL('image/jpeg', 0.92);
				link.click();
				showToast('Pobrano JPG!');
			} else {
				var imgData = canvas.toDataURL('image/jpeg', 0.92);
				var doc     = new jspdf.jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
				var pageW   = 210;
				var margin  = 20;
				var imgW    = pageW - margin * 2;
				var imgH    = (canvas.height * imgW) / canvas.width;
				doc.addImage(imgData, 'JPEG', margin, margin, imgW, imgH);
				doc.save(exportFilename + '.pdf');
				showToast('Pobrano PDF!');
			}
			if (btn) {
				btn.disabled    = false;
				btn.textContent = format === 'jpg' ? '⬇️ Pobierz JPG' : '⬇️ Pobierz PDF';
			}
		}).catch(function () {
			showToast('Błąd eksportu karty.', 4000);
			if (btn) {
				btn.disabled    = false;
				btn.textContent = format === 'jpg' ? '⬇️ Pobierz JPG' : '⬇️ Pobierz PDF';
			}
		});
	}

	/* ============================================================
	   Init
	   ============================================================ */

	$(document).ready(function () {

		// Main generate
		var submitBtn = document.getElementById('bwg-submit-btn');
		if (submitBtn) {
			submitBtn.addEventListener('click', function () { generateWishes(false); });
		}

		// Zaskocz mnie
		var surpriseBtn = document.getElementById('bwg-surprise-btn');
		if (surpriseBtn) {
			surpriseBtn.addEventListener('click', surprise);
		}

		// Length → rhyme sync
		var lengthSel = document.getElementById('bwg_length');
		if (lengthSel) {
			lengthSel.addEventListener('change', function () {
				updateRhymeToggle(this.value);
			});
		}

		// Improve section accordion
		var improveToggle = document.getElementById('bwg-improve-toggle');
		var improveBody   = document.getElementById('bwg-improve-body');
		if (improveToggle && improveBody) {
			improveToggle.addEventListener('click', function () {
				var expanded = this.getAttribute('aria-expanded') === 'true';
				this.setAttribute('aria-expanded', String(!expanded));
				improveBody.style.display = expanded ? 'none' : 'block';
				var arrow = this.querySelector('.bwg-accordion-arrow');
				if (arrow) arrow.textContent = expanded ? '▾' : '▴';
			});
		}

		// Improve button
		var improveBtn = document.getElementById('bwg-improve-btn');
		if (improveBtn) {
			improveBtn.addEventListener('click', improveWish);
		}

		// Improve result actions
		var improveCopy = document.getElementById('bwg-improve-copy');
		if (improveCopy) {
			improveCopy.addEventListener('click', function () {
				var t = document.getElementById('bwg-improve-text-out');
				copyText(t ? t.innerText : '');
			});
		}

		var improvePreview = document.getElementById('bwg-improve-preview');
		if (improvePreview) {
			improvePreview.addEventListener('click', function () {
				var t = document.getElementById('bwg-improve-text-out');
				openModal(t ? t.innerText : '', 'zyczenia-ulepszone');
			});
		}

		// Modal: overlay / close
		var modalOverlay = document.getElementById('bwg-modal-overlay');
		var modalClose   = document.getElementById('bwg-modal-close');
		if (modalOverlay) modalOverlay.addEventListener('click', closeModal);
		if (modalClose)   modalClose.addEventListener('click', closeModal);

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') closeModal();
		});

		// Modal: template picker
		var picker = document.getElementById('bwg-template-picker');
		if (picker) {
			picker.addEventListener('click', function (e) {
				var btn = e.target.closest('.bwg-tpl-btn');
				if (!btn) return;
				var tplId = btn.dataset.tpl;
				var tpl   = CARD_TEMPLATES.find(function (t) { return t.id === tplId; });
				if (!tpl) return;
				selectedTemplate = tpl;
				// Update active state
				picker.querySelectorAll('.bwg-tpl-btn').forEach(function (b) {
					b.classList.toggle('bwg-tpl-btn--active', b.dataset.tpl === tplId);
				});
				applyTemplateToPreview(tpl);
			});
		}

		// Modal: download buttons
		var modalJpg = document.getElementById('bwg-modal-jpg');
		var modalPdf = document.getElementById('bwg-modal-pdf');
		if (modalJpg) modalJpg.addEventListener('click', function () { exportCard('jpg'); });
		if (modalPdf) modalPdf.addEventListener('click', function () { exportCard('pdf'); });

	}); // end ready

})(jQuery);
