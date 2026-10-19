import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    static targets = ['menu', 'button'];

    toggle() {
        const isOpen = this.menuTarget.classList.toggle('hidden') === false;

        this.buttonTarget.setAttribute('aria-expanded', String(isOpen));
        this.buttonTarget.setAttribute(
            'aria-label',
            isOpen ? 'Fermer le menu' : 'Ouvrir le menu'
        );
    }
}
