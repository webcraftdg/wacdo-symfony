import { Controller } from '@hotwired/stimulus';
import flatpickr from 'flatpickr';
import { French } from 'flatpickr/dist/l10n/fr.js';

export default class extends Controller {
    connect() {
        this.datepicker = flatpickr(this.element, {
            locale: French,
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: false,
        });
    }

    disconnect() {
        this.datepicker?.destroy();
    }
}
