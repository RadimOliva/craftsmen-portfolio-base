(() => {
    function initialize() {
        document.querySelectorAll('.craft-delete-enquiry').forEach(form => {
            const button = form.querySelector('button');
            button.disabled = false;
            form.addEventListener('submit', event => {
                if (!window.confirm('Opravdu chcete trvale smazat tuto poptávku včetně všech příloh? Tuto akci nelze vrátit zpět.')) {
                    event.preventDefault();
                }
            });
        });
        if (document.querySelector('[name=expires_european]') && window.jQuery?.fn.datepicker) {
            jQuery('#craft-expires').datepicker({dateFormat:'dd.mm.yy',firstDay:1,changeMonth:true,changeYear:true});
        }
        const logo = document.querySelector('#masthead #logo');
        if (logo && window.ProcessWire?.config?.craftAdminTitle) {
            const name = document.createElement('strong');
            name.textContent = ProcessWire.config.craftAdminTitle;
            const label = document.createElement('span');
            label.textContent = 'Administrace';
            logo.replaceChildren(name, label);
            logo.classList.add('craft-admin-brand');
        }
        if (document.querySelector('.craft-rich-text') && window.tinymce) {
            tinymce.init({
                selector: '.craft-rich-text',
                base_url: '/wire/modules/Inputfield/InputfieldTinyMCE/tinymce-6.8.2', suffix: '.min',
                language: 'cs', language_url: '/wire/modules/Inputfield/InputfieldTinyMCE/langs/cs.js',
                plugins: 'lists', toolbar: 'undo redo | bold italic | bullist numlist | removeformat',
                menubar: false, statusbar: false, branding: false, promotion: false, height: 280,
                valid_elements: 'p,br,strong,b,em,i,ul,ol,li',
                paste_as_text: true,
                content_style: 'body{font:16px/1.6 Arial,sans-serif;color:#26372f}p{margin:0 0 12px}ul,ol{padding-left:24px}',
                setup: editor => editor.on('change', () => editor.save())
            });
        }
        const closePickers = (except = null) => document.querySelectorAll('.craft-icon-picker').forEach(picker => {
            if (picker === except) return;
            picker.querySelector('.craft-icon-grid').hidden = true;
            picker.querySelector('.craft-icon-trigger').setAttribute('aria-expanded', 'false');
        });
        document.addEventListener('click', event => {
            const picker = event.target.closest('.craft-icon-picker');
            closePickers(picker);
            if (!picker) return;
            const trigger = picker.querySelector('.craft-icon-trigger');
            const grid = picker.querySelector('.craft-icon-grid');
            if (event.target.closest('.craft-icon-trigger')) {
                grid.hidden = !grid.hidden;
                trigger.setAttribute('aria-expanded', String(!grid.hidden));
                if (!grid.hidden) grid.querySelector('[aria-pressed="true"]').focus();
            }
            const choice = event.target.closest('.craft-icon-choice');
            if (choice) {
                picker.querySelector('input').value = choice.dataset.icon;
                trigger.replaceChildren(choice.firstElementChild.cloneNode(true));
                trigger.setAttribute('aria-label', `Vybrat ikonu: ${choice.getAttribute('aria-label')}`);
                grid.querySelectorAll('button').forEach(button => button.setAttribute('aria-pressed', String(button === choice)));
                closePickers();
                trigger.focus();
            }
        });
        document.addEventListener('keydown', event => {
            const picker = event.target.closest('.craft-icon-picker');
            if (picker && event.key === 'Escape') {
                closePickers();
                picker.querySelector('.craft-icon-trigger').focus();
                event.preventDefault();
            }
        });
        document.addEventListener('focusin', event => closePickers(event.target.closest('.craft-icon-picker')));
        document.querySelectorAll('.craft-trust-form').forEach(form => {
            const items = form.querySelector('.craft-trust-items');
            const add = form.querySelector('.craft-trust-add');
            let next = items.children.length;
            const update = () => {
                add.disabled = items.children.length >= 4;
                form.querySelector('.craft-trust-count').textContent = `${items.children.length} / 4 výhody`;
            };
            add.addEventListener('click', () => {
                if (items.children.length >= 4) return;
                items.insertAdjacentHTML('beforeend', form.querySelector('template').innerHTML.replaceAll('__INDEX__', String(next++)));
                update();
                items.lastElementChild.querySelector('input').focus();
            });
            items.addEventListener('click', event => {
                if (event.target.closest('.craft-trust-remove')) {
                    event.target.closest('.craft-trust-card').remove();
                    update();
                }
            });
            update();
        });
        document.querySelectorAll('.craft-visibility-form').forEach(form => {
            const toggle = form.querySelector('input[type="checkbox"]');
            const status = form.querySelector('.craft-save-status');
            let saved = toggle.checked;
            let saving = false;
            form.addEventListener('submit', event => event.preventDefault());
            toggle.addEventListener('change', async () => {
                if (saving) return;
                const body = new FormData(form);
                body.set('visibility_autosave', '1');
                saving = true;
                toggle.disabled = true;
                form.setAttribute('aria-busy', 'true');
                status.textContent = 'Ukládám…';
                status.classList.remove('is-error');
                try {
                    const response = await fetch(form.action || location.href, {
                        method: 'POST', body, credentials: 'same-origin',
                        headers: { Accept: 'application/json' }
                    });
                    if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) throw new Error('Save failed');
                    const result = await response.json();
                    if (result.saved !== true || typeof result.enabled !== 'boolean') throw new Error('Save failed');
                    saved = result.enabled;
                    toggle.checked = saved;
                    status.textContent = 'Uloženo.';
                } catch {
                    toggle.checked = saved;
                    status.textContent = 'Uložení se nepodařilo potvrdit. Obnovte stránku a ověřte stav.';
                    status.classList.add('is-error');
                } finally {
                    saving = false;
                    toggle.disabled = false;
                    form.removeAttribute('aria-busy');
                }
            });
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
