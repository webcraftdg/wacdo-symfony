import { Controller } from '@hotwired/stimulus';
import * as Turbo from '@hotwired/turbo';
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
        archivedUrl: String,
        contextName:String,
        mode: { type: String, default: 'reload' },
        csrf:String
    }

   async confirmDelete() {
        if (confirm('Attention, cet élément sera archivé : '+this.contextNameValue+' !')) {
            try {
                const headers = {};

                if (this.modeValue === 'turbo') {
                    headers['Accept'] = 'text/vnd.turbo-stream.html';
                }

                const response = await fetch(this.archivedUrlValue, {
                    method: 'PATCH',
                    headers,
                    body: new URLSearchParams({
                        _token: this.csrfValue
                    })
                });

                if (!response.ok) {
                    throw new Error(`Une erreur c'est produite : statut de réponse : ${response.status}`);
                }
                if (String(this.modeValue) === 'turbo') {
                    Turbo.renderStreamMessage(await response.text());
                } else {
                    window.location.reload();
                }
            } catch (erreur) {
                alert(erreur.message);
            }
        }
    }
}
