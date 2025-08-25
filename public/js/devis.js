// on charge les marques au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // on récupère les marques depuis l'API
    document.getElementById('marque').addEventListener('change', function() {
        const marque = this.value;
        console.log('Marque sélectionnée :', marque);

        // mettre à jour le champ caché pour la marque
        const marqueHidden = document.querySelector('[name="devis_type_form[vehicule][marque]"]');
        if (marqueHidden) {
            marqueHidden.value = marque;
            console.log('Champ caché marque mis à jour :', marqueHidden.value);
        } else {
            console.log('Champ caché marque introuvable');
        }

        // appel à l'API pour récupérer les modèles associés à la marque
        fetch('/client/api/modeles?marque=' + encodeURIComponent(marque))
            .then(response => response.json())
            .then(data => {
                console.log('Modèles reçus :', data);
                const datalist = document.getElementById('modeles-list');
                datalist.innerHTML = '';
                if (data.length === 0) {
                    datalist.innerHTML = `<option value="Pas de modèle associé"></option>`;
                } else {
                    data.forEach(modele => {
                        datalist.innerHTML += `<option value="${modele.Model_Name}"></option>`;
                    });
                }
            });
    });

    // on met à jour le champ caché pour le modèle sélectionné
    document.getElementById('modele').addEventListener('change', function() {
        const modele = this.value;
        console.log('Modèle sélectionné :', modele);

        const modeleHidden = document.querySelector('[name="devis_type_form[vehicule][modele]"]');
        if (modeleHidden) {
            modeleHidden.value = modele;
            console.log('Champ caché modèle mis à jour :', modeleHidden.value);
        } else {
            console.log('Champ caché modèle introuvable');
        }
    });

 // création les animations du formulaire
    const formSteps = Array.from(document.querySelectorAll('.form-step'));
    const progressSteps = Array.from(document.querySelectorAll('.progress-bar-form .step'));
    const progressLines = Array.from(document.querySelectorAll('.progress-bar-form .progress-line'));
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('client-form');
    
    let currentStep = 0;
    
    // initialise les étapes du formulaire en ajoutant active si l'index correspond à l'étape actuelle
    function updateFormSteps() {
        formSteps.forEach((step, index) => {
            step.classList.toggle('active', index === currentStep);
        });
    }
    
    // met à jour la barre de progression en ajoutant active ou completed selon l'étape actuelle
    function updateProgressBar() {
        progressSteps.forEach((step, index) => {
            if (index < currentStep) {
                step.classList.add('completed');
                step.classList.remove('active');
            } else if (index === currentStep) {
                step.classList.add('active');
                step.classList.remove('completed');
            } else {
                step.classList.remove('active', 'completed');
            }
        });
        
        progressLines.forEach((line, index) => {
            line.classList.toggle('active', index < currentStep);
        });
    }
    
    // met à jour les boutons de navigation en fonction de l'étape actuelle
    function updateNavigationButtons() {
        prevBtn.style.display = currentStep === 0 ? 'none' : 'inline-block';
        // Affiche le bouton "Suivant" sauf à la dernière étape
        nextBtn.style.display = currentStep < formSteps.length - 1 ? 'inline-block' : 'none';
        // Affiche le bouton "Valider" seulement à la dernière étape
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.style.display = currentStep === formSteps.length - 1 ? 'inline-block' : 'none';
        }
    }
    
    // validation de l'étape actuelle
    function validateStep(stepIndex) {
        const currentFormStep = formSteps[stepIndex];
        const inputs = Array.from(currentFormStep.querySelectorAll('input[required], select[required], textarea[required]'));
        let isValid = true;
        
        // gestion des erreurs
        currentFormStep.querySelectorAll('.error-message').forEach(el => el.remove());
        inputs.forEach(input => input.classList.remove('invalid'));
        
        // vérification classique des champs requis
        inputs.forEach(input => {
            let fieldValid = true;
            if (input.type === 'checkbox' && !input.checked) {
                fieldValid = false;
            } else if (input.value.trim() === '') {
                fieldValid = false;
            } else if (input.type === 'email' && !/^\S+@\S+\.\S+$/.test(input.value)) {
                fieldValid = false;
            } 
            if (!fieldValid) {
                isValid = false;
                input.classList.add('invalid');
                const errorMsg = document.createElement('p');
                errorMsg.className = 'error-message';
                errorMsg.textContent = input.type === 'checkbox' ? 'Ce champ est requis.' : 'Veuillez entrer une donnée valide.';
                input.parentNode.appendChild(errorMsg);
            }
        });

        // validation spécifique pour le champ téléphone 
        const telInput = currentFormStep.querySelector('input[type="tel"]');
        if (telInput && telInput.value.trim() !== '' && !/^[\d+\s\-()]+$/.test(telInput.value)) {
            console.log('Numéro de téléphone invalide détecté !');
            isValid = false;
            telInput.classList.add('invalid');
            const errorMsg = document.createElement('p');
            errorMsg.className = 'error-message';
            errorMsg.textContent = 'Veuillez entrer un numéro de téléphone valide.';
            telInput.parentNode.appendChild(errorMsg);
        }

        // validation spécifique pour tous les champs number 
        const numberInputs = Array.from(currentFormStep.querySelectorAll('input[type="number"]'));
        numberInputs.forEach(numberInput => {
            // si le champ n'est pas vide et que ce n'est pas un nombre entier, ou si le champ est invalide selon HTML5
            if (
                (numberInput.value.trim() !== '' && !/^\d+$/.test(numberInput.value.trim())) ||
                !numberInput.validity.valid
            ) {
                isValid = false;
                numberInput.classList.add('invalid');
                const errorMsg = document.createElement('p');
                errorMsg.className = 'error-message';
                errorMsg.textContent = 'Veuillez saisir un nombre entier valide.';
                numberInput.parentNode.appendChild(errorMsg);
            }
        });

        return isValid;
    }
    
    // gerer le recapitulatif 
    function populateReviewDetails() {
        const formData = new FormData(form);
        const reviewData = {
            nom: formData.get('devis_type_form[nom]') || 'Non renseigné',
            prenom: formData.get('devis_type_form[prenom]') || 'Non renseigné',
            email: formData.get('devis_type_form[email]') || 'Non renseigné',
            tel: formData.get('devis_type_form[tel]') || 'Non renseigné',
            marque: formData.get('marque') || 'Non renseigné',
            modele: formData.get('modele') || 'Non renseigné',
            anneeFabrication: formData.get('devis_type_form[vehicule][anneeFabrication]') || 'Non renseigné',
            km: formData.get('devis_type_form[vehicule][km]') || 'Non renseigné',
            carburant: (() => {
            const select = document.getElementById('devis_type_form_vehicule_carburant');
            if (select && select.selectedIndex >= 0) {
                const selected = select.options[select.selectedIndex];
                // Vérifie si la valeur est vide ou si le texte correspond à l'option par défaut
                if (
                    !selected.value ||
                    selected.value === "" ||
                    selected.textContent.trim().toLowerCase().includes("sélectionner")
                ) {
                    return "Non renseigné";
                }
                return selected.textContent;
            }
            return "Non renseigné";
        })(),
            prestation: (() => {
                    const select = document.getElementById('devis_type_form_prestation');
                    if (select) {
                        const selected = select.options[select.selectedIndex];
                        return selected ? selected.textContent : 'Non renseigné';
                    }
                    return 'Non renseigné';
                })(),            
            text: formData.get('devis_type_form[text]') || 'Non renseigné',  
        };
        
        for (const key in reviewData) {
            const element = document.querySelector(`#reviewDetails [data-review="${key}"]`);
            if (element) {
                element.textContent = reviewData[key];
            }
        }
    }
    
    // permet de passer à l'étape suivante si la validation est réussie
    nextBtn.addEventListener('click', () => {
        if (validateStep(currentStep)) {
            currentStep++;
            if (currentStep === formSteps.length - 1) { 
                populateReviewDetails();
            }
            updateFormSteps();
            updateProgressBar();
            updateNavigationButtons();
        }
    });
    
    // permet de revenir à l'étape précédente
    prevBtn.addEventListener('click', () => {
        if (currentStep > 0) {
            currentStep--;
            updateFormSteps();
            updateProgressBar();
            updateNavigationButtons();
        }
    });
    
    // inititalise 
    updateFormSteps();
    updateProgressBar();
    updateNavigationButtons();
});