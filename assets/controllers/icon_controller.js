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
        name: String,
        width: { type: Number, default: 24 },
        height: { type: Number, default: 24 },
    }

    async connect() {
        const response = await fetch(`/icons/${this.nameValue}.svg`);

        if (!response.ok) {
            console.error(`Icône "${this.nameValue}" introuvable.`);
            return;
        }

        let svg = await response.text();

        svg = svg.replace('<svg', `<svg width="${this.widthValue}" height="${this.heightValue}"`);

        this.element.innerHTML = svg;
    }
}
