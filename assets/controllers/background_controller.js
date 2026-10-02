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

    static values = {
        active: { type: Boolean, default: false },
        classes: String
    }

    async connect() {
        if (this.activeValue && this.classesValue) {
            this.element.classList.add(this.classesValue);
        }
    }
}
