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

    static targets = [
        "modal",
        "content"
    ];
    static values = {
        url:String
    }

    connect() {
        this.close()
    }

    async open() {
        try {
            const reponse = await fetch(this.urlValue, {
                method: 'GET'
            });

            if (!reponse.ok) {
                throw new Error(
                    `Une erreur s'est produite : statut ${reponse.status}`
                );
            }

            const html = await reponse.text();
            if (this.contentTarget) {
                this.contentTarget.innerHTML = html;
                this.modalTarget.classList.remove('hidden');

                const form = this.contentTarget.querySelector('form');

                if (!form) {
                    throw new Error('Aucun formulaire trouvé dans la modale');
                }
                this.subscribeForm(form);
            } else {
                throw new Error(
                    'Le "content" est indisponible'
                );
            }


        } catch (erreur) {
            alert(erreur.message);
        }
    }

    async submit(event) {
        event.preventDefault();

        const form = event.currentTarget;
        const formData = new FormData(form);

        const response = await fetch(form.action, {
            method: form.method,
            body: formData
        });

        if (response.status === 422) {
            this.contentTarget.innerHTML = await response.text();

            // Attention : le formulaire vient d'être remplacé.
            // Il faut réinstaller son écouteur submit.
            const form = this.contentTarget.querySelector('form');
            this.subscribeForm(form);
            // name="assignment[user]
            return;
        }

        if (response.status === 201) {
            const result = await response.json();
            this.manageResponse(result)
            console.log(result);
            this.close();
        }
    }

    manageResponse(user)
    {

        const select = this.element.querySelector('#assignment_user');

        const tomSelect = select.tomselect;
        if (tomSelect) {
            tomSelect.addOption({
                value: String(user.id),
                text: `${user.lastname} - ${user.firstname}`
            });

            tomSelect.setValue(String(user.id));
        } else if(select) {
            select.add(new Option(
                `${user.lastname} - ${user.firstname}`,
                String(user.id),
                true,
                true
            ));

            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

    }

    close() {
        this.modalTarget.classList.add('hidden');
    }

    subscribeForm(form)
    {
        form?.addEventListener('submit', this.submit.bind(this));
    }

}
