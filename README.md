# 🚑 PSE 2024 - Fiches Aide-Mémoire Secourisme

Site web responsive de fiches aide-mémoire basées sur les **recommandations PSE 2024 officielles** pour les secouristes bénévoles.

## 📋 Description

Application web légère et intuitive permettant aux secouristes d'accéder rapidement aux procédures essentielles sur le terrain, notamment sur smartphone.

### ✨ Fonctionnalités

- ✅ **Responsive** : optimisé pour smartphone, tablette et desktop
- ✅ **Recherche rapide** : trouvez une fiche en quelques secondes
- ✅ **Navigation par catégories** : accès organisé aux fiches
- ✅ **Mode lecture optimisé** : affichage clair et détaillé des procédures
- ✅ **Accessibilité** : navigation au clavier, lecteurs d'écran
- ✅ **Pas de connexion requise** : fonctionne en mode hors ligne une fois chargé

## 🎯 Catégories de fiches

1. **🚨 Urgences Vitales**
   - Arrêt cardiaque (adulte, enfant, nourrisson)
   - Hémorragies externes
   - Détresse respiratoire

2. **🩹 Traumatologie**
   - Traumatismes crâniens
   - Brûlures
   - Plaies graves

3. **😵 Malaises**
   - Malaise général
   - AVC (Accident Vasculaire Cérébral)
   - Crises convulsives

4. **⚕️ Gestes Techniques**
   - Position Latérale de Sécurité (PLS)
   - Utilisation du DAE
   - Techniques de compression

5. **👶 Pédiatrie**
   - Spécificités enfant et nourrisson
   - RCP pédiatrique

6. **📋 Situations Particulières**
   - Obstruction des voies aériennes (étouffement)
   - Noyade
   - Hypothermie

## 🚀 Utilisation

### Accès rapide

1. Ouvrez `index.html` dans votre navigateur
2. Utilisez la barre de recherche ou naviguez par catégorie
3. Cliquez sur une fiche pour voir le détail
4. Raccourci clavier : appuyez sur `/` pour accéder directement à la recherche

### Sur smartphone

- Ajoutez le site à l'écran d'accueil pour un accès encore plus rapide
- L'interface s'adapte automatiquement à la taille de l'écran
- Fonctionne même sans connexion internet (après premier chargement)

## 📱 Installation sur smartphone

### iPhone (Safari)
1. Ouvrez le site dans Safari
2. Appuyez sur l'icône "Partager"
3. Sélectionnez "Sur l'écran d'accueil"

### Android (Chrome)
1. Ouvrez le site dans Chrome
2. Appuyez sur les 3 points (menu)
3. Sélectionnez "Ajouter à l'écran d'accueil"

## 📝 Ajouter ou modifier des fiches

Les fiches sont stockées dans `fiches-data.js`. Pour ajouter une nouvelle fiche :

```javascript
{
    id: 'identifiant-unique',
    titre: 'Titre de la fiche',
    categorie: 'urgences-vitales', // ou traumatologie, malaises, techniques, pediatrie, situations-particulieres
    icon: '🚨',
    description: 'Description courte',
    tags: ['tag1', 'tag2'],
    contenu: {
        indication: 'Quand utiliser cette procédure',
        signes: ['signe 1', 'signe 2'],
        procedure: [
            {
                titre: 'Étape 1',
                etapes: ['action 1', 'action 2']
            }
        ],
        alertes: [
            {
                type: 'danger', // ou 'info' ou 'alert'
                texte: 'Message important'
            }
        ]
    }
}
```

## 📚 Sources

Les fiches sont basées sur les **recommandations officielles PSE 2024** :

- **Référentiel PSE 2024** - Direction Générale de la Sécurité Civile et de la Gestion des Crises (DGSCGC)
- **Recommandations PSC1 2024** - Référentiel national
- Sources officielles : [secourisme.net](https://www.secourisme.net), [interieur.gouv.fr](https://www.interieur.gouv.fr)

### Structure du PSE 2024

Le référentiel PSE 2024 est organisé en 11 chapitres :
- A - Attitude et comportement
- B - Protection et sécurité
- C - Hygiène et asepsie
- D - Bilans
- E - Malaises et affections spécifiques
- F - Traumatismes
- G - Souffrance psychique et comportements inhabituels
- H - Relevage et brancardage
- I - Situations particulières
- J - Fiches optionnelles
- K - Divers

## ⚠️ Avertissement Important

**Ces fiches sont des aide-mémoire et ne remplacent pas :**
- Une formation complète aux premiers secours (PSC1, PSE1, PSE2)
- Le jugement et l'expérience du secouriste
- Les consignes données par le médecin régulateur (SAMU)

**En cas de doute :**
- Appelez le 15 (SAMU) ou le 18 (Pompiers)
- Demandez un avis médical
- Appliquez les consignes du centre de régulation

## 🛠️ Technologies utilisées

- **HTML5** : structure sémantique
- **CSS3** : design responsive avec variables CSS
- **JavaScript vanilla** : aucune dépendance externe
- **Mobile-first** : optimisé pour les smartphones

## 📄 Licence et responsabilité

Ce projet est un outil d'aide aux secouristes formés. Il doit être utilisé en complément d'une formation officielle et ne peut en aucun cas remplacer celle-ci.

Les informations contenues dans ces fiches sont issues des recommandations officielles PSE 2024. En cas de divergence, le référentiel officiel fait foi.

## 🔄 Mises à jour

Les recommandations de secourisme évoluent régulièrement. Ce site doit être mis à jour à chaque nouvelle version du référentiel PSE.

**Version actuelle** : PSE 2024 (recommandations décembre 2023)

## 📞 Numéros d'urgence

- **15** : SAMU (urgences médicales)
- **18** : Pompiers
- **112** : Numéro d'urgence européen
- **114** : Numéro d'urgence par SMS (personnes sourdes ou malentendantes)

---

**Développé pour les secouristes bénévoles** 🚑

*Restez formés, restez vigilants, sauvez des vies.*
