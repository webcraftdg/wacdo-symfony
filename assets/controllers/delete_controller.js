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
        deleteUrl: String,
        contextName:String,
        csrf:String
    }

   async confirmDelete() {
        if (confirm('Attention, la suppression de l\'élément : '+this.contextNameValue+' sera définitive !')) {
            try {
                const reponse = await fetch(this.deleteUrlValue, {
                    method: 'DELETE',
                });
                if (!reponse.ok) {
                    throw new Error("Une erreur c'est produite : statut de réponse : ${reponse.status}");
                }
                this.element.remove();
            } catch (erreur) {
                alert(erreur.message);
            }
        }
    }
}
