// Donate form: ticket option per campaign, amount presets, online/offline mode.
// Progressive enhancement only — the server validates and prices everything.
export function initDonateForms() {
    document.querySelectorAll('[data-donate-form]').forEach((form) => {
        const campaign = form.querySelector('[data-donate-campaign]');
        const ticketBlock = form.querySelector('[data-ticket-block]');
        const amountBlock = form.querySelector('[data-amount-block]');
        const quantityBlock = form.querySelector('[data-quantity-block]');
        const amountInput = form.querySelector('[data-amount-input]');
        const modeInput = form.querySelector('[data-mode-input]');

        const syncType = () => {
            const ticket = form.querySelector('[data-type-radio]:checked')?.value === 'ticket';
            if (amountBlock) amountBlock.hidden = ticket;
            if (quantityBlock) quantityBlock.hidden = !ticket;
        };

        const syncCampaign = () => {
            let hasTicket = false;
            form.querySelectorAll('[data-ticket-option]').forEach((opt) => {
                const match = opt.dataset.ticketOption === campaign.value;
                opt.hidden = !match;
                hasTicket = hasTicket || match;
                if (!match) opt.querySelector('input').checked = false;
            });
            if (ticketBlock) ticketBlock.hidden = !hasTicket;
            if (!form.querySelector('[data-type-radio]:checked')) {
                const donation = form.querySelector('[data-type-radio][value="donation"]');
                if (donation) donation.checked = true;
            }
            syncType();
        };

        campaign?.addEventListener('change', syncCampaign);
        form.querySelectorAll('[data-type-radio]').forEach((r) => r.addEventListener('change', syncType));

        form.querySelectorAll('[data-amount-preset]').forEach((btn) => btn.addEventListener('click', () => {
            amountInput.value = btn.dataset.amountPreset;
            form.querySelectorAll('[data-amount-preset]').forEach((b) => b.classList.toggle('is-selected', b === btn));
            amountInput.focus();
        }));

        form.querySelectorAll('[data-payment-toggle] [data-mode]').forEach((btn) => btn.addEventListener('click', () => {
            if (modeInput) modeInput.value = btn.dataset.mode;
        }));

        if (campaign) syncCampaign();
    });
}
