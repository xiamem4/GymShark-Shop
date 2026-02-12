import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form', 'total'];

    async update(event) {
        const formData = new FormData(this.formTarget);
        
        // Envoi de la requête AJAX au serveur
        const response = await fetch(this.formTarget.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.ok) {
            // Ici, on pourrait soit recharger un fragment HTML avec Turbo, 
            // soit mettre à jour les valeurs manuellement si le contrôleur renvoie du JSON.
            // Pour faire simple avec votre code actuel, on recharge la zone du panier :
            window.location.reload(); 
            // Note : Pour éviter le reload, le contrôleur PHP devrait idéalement renvoyer du JSON.
        }
    }
}