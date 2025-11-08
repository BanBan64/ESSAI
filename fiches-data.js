// Données des fiches PSE 2024
// Basé sur les recommandations officielles PSE 2024
// Sources : secourisme.net, interieur.gouv.fr, recommandations PSC1/PSE 2024

const fichesData = [
    // ==========================================
    // URGENCES VITALES
    // ==========================================
    {
        id: 'arret-cardiaque-adulte',
        titre: 'Arrêt cardiaque chez l\'adulte',
        categorie: 'urgences-vitales',
        icon: '🫀',
        description: 'Procédure de réanimation cardio-pulmonaire (RCP) chez l\'adulte',
        tags: ['urgence absolue', 'RCP', 'DAE', 'massage cardiaque'],
        contenu: {
            indication: 'Victime adulte inconsciente qui ne respire pas ou présente une respiration anormale (gasps)',
            procedure: [
                {
                    titre: 'Débuter immédiatement la RCP',
                    etapes: [
                        'Installer la victime sur le dos sur un plan dur',
                        'Réaliser 30 compressions thoraciques',
                        'Puis 2 insufflations',
                        'Répéter ces cycles sans interruption'
                    ]
                },
                {
                    titre: 'Compressions thoraciques',
                    etapes: [
                        'Placer le talon d\'une main au centre de la poitrine',
                        'Placer l\'autre main au-dessus',
                        'Bras tendus, épaules à l\'aplomb du sternum',
                        'Comprimer de 5 à 6 cm de profondeur',
                        'Fréquence : 100 à 120 compressions par minute',
                        'Laisser le thorax reprendre sa position initiale entre chaque compression'
                    ]
                },
                {
                    titre: 'Mise en place du DAE',
                    etapes: [
                        'Installer le DAE sans interrompre la RCP',
                        'Coller les électrodes sur la peau nue de la victime',
                        'Suivre les instructions vocales de l\'appareil',
                        'Ne toucher la victime que sur instruction du DAE',
                        'Si choc recommandé : s\'écarter et laisser le DAE choquer',
                        'Reprendre immédiatement la RCP après le choc'
                    ]
                },
                {
                    titre: 'Relais des secouristes',
                    etapes: [
                        'Se relayer toutes les 2 minutes',
                        'Effectuer le changement lors de l\'analyse du DAE',
                        'Minimiser les interruptions des compressions'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'Ne jamais interrompre la RCP sauf sur instruction du DAE ou reprise de signes de vie'
                },
                {
                    type: 'info',
                    texte: 'En équipe, un sauveteur installe le DAE pendant qu\'un autre poursuit la RCP'
                }
            ],
            arret: 'Arrêter la RCP uniquement si la victime reprend une respiration normale ou si un médecin le demande'
        }
    },
    {
        id: 'arret-cardiaque-isole',
        titre: 'Arrêt cardiaque en sauveteur isolé',
        categorie: 'urgences-vitales',
        icon: '🚨',
        description: 'Conduite à tenir en cas d\'arrêt cardiaque lorsque vous êtes seul',
        tags: ['urgence absolue', 'sauveteur isolé', 'alerte', 'RCP'],
        contenu: {
            indication: 'Victime en arrêt cardiaque et vous êtes seul',
            procedure: [
                {
                    titre: 'Alerter immédiatement',
                    etapes: [
                        'Appeler ou faire appeler le 15 ou le 18',
                        'Demander un DAE si disponible à proximité',
                        'Puis débuter immédiatement la RCP'
                    ]
                },
                {
                    titre: 'RCP en sauveteur isolé',
                    etapes: [
                        'Réaliser 30 compressions thoraciques',
                        'Puis 2 insufflations',
                        'Continuer ces cycles sans interruption',
                        'Installer le DAE dès son arrivée'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'Ne pas quitter la victime pour aller chercher du secours. Alerter par téléphone ou par un témoin.'
                }
            ]
        }
    },
    {
        id: 'arret-cardiaque-enfant',
        titre: 'Arrêt cardiaque chez l\'enfant ou le nourrisson',
        categorie: 'urgences-vitales',
        icon: '👶',
        description: 'RCP adaptée à l\'enfant (1 à 8 ans) et au nourrisson (moins de 1 an)',
        tags: ['pédiatrie', 'RCP', 'enfant', 'nourrisson'],
        contenu: {
            indication: 'Enfant (1-8 ans) ou nourrisson (< 1 an) inconscient qui ne respire pas',
            procedure: [
                {
                    titre: 'Débuter la RCP',
                    etapes: [
                        'Réaliser 5 insufflations initiales',
                        'Puis alterner 15 compressions et 2 insufflations',
                        'Alerter après 1 minute de RCP si vous êtes seul'
                    ]
                },
                {
                    titre: 'Compressions - Enfant',
                    etapes: [
                        'Utiliser une ou deux mains selon la corpulence',
                        'Comprimer d\'environ 1/3 de l\'épaisseur du thorax (5 cm)',
                        'Fréquence : 100 à 120 compressions par minute'
                    ]
                },
                {
                    titre: 'Compressions - Nourrisson',
                    etapes: [
                        'Utiliser la technique des 2 doigts ou des 2 pouces',
                        'Comprimer d\'environ 1/3 de l\'épaisseur du thorax (4 cm)',
                        'Fréquence : 100 à 120 compressions par minute'
                    ]
                },
                {
                    titre: 'DAE pédiatrique',
                    etapes: [
                        'Privilégier un DAE avec électrodes pédiatriques si disponible',
                        'Sinon utiliser un DAE adulte',
                        'Chez le nourrisson : placer une électrode sur le thorax, l\'autre dans le dos'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'info',
                    texte: 'Chez l\'enfant et le nourrisson, commencer par 5 insufflations avant les compressions'
                }
            ]
        }
    },
    {
        id: 'hemorragie-externe',
        titre: 'Hémorragie externe',
        categorie: 'urgences-vitales',
        icon: '🩸',
        description: 'Arrêt d\'une hémorragie externe',
        tags: ['urgence vitale', 'saignement', 'compression', 'garrot'],
        contenu: {
            indication: 'Saignement abondant qui ne s\'arrête pas spontanément',
            procedure: [
                {
                    titre: 'Compression manuelle',
                    etapes: [
                        'Appuyer fortement sur l\'endroit qui saigne',
                        'Interposer un tissu propre si possible (mouchoir, vêtement)',
                        'Recouvrir complètement la plaie',
                        'Maintenir la compression jusqu\'à l\'arrivée des secours',
                        'Si pas de tissu : appuyer directement avec la main'
                    ]
                },
                {
                    titre: 'Pansement compressif',
                    etapes: [
                        'À réaliser uniquement si la compression manuelle a été efficace',
                        'Placer une épaisseur de tissu propre sur la plaie',
                        'Fixer avec une bande élastique ou un lien large',
                        'Serrer suffisamment pour arrêter le saignement',
                        'Vérifier l\'efficacité du pansement'
                    ]
                },
                {
                    titre: 'Si compression inefficace',
                    etapes: [
                        'Zone garrotable (membre) : poser un garrot',
                        'Zone non garrotable : utiliser une gaze hémostatique si disponible',
                        'Maintenir la compression en attendant les secours'
                    ]
                },
                {
                    titre: 'Surveillance',
                    etapes: [
                        'Vérifier l\'efficacité des gestes réalisés',
                        'Surveiller l\'apparition de signes de détresse circulatoire',
                        'Allonger la victime',
                        'Rassurer et protéger du froid'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'Ne jamais retirer un corps étranger enfoncé dans une plaie'
                },
                {
                    type: 'info',
                    texte: 'La victime ou un témoin peut réaliser la compression si le sauveteur doit alerter'
                }
            ]
        }
    },
    {
        id: 'detresse-respiratoire',
        titre: 'Détresse respiratoire',
        categorie: 'urgences-vitales',
        icon: '😮‍💨',
        description: 'Prise en charge d\'une détresse respiratoire',
        tags: ['détresse', 'respiration', 'oxygène', 'ventilation'],
        contenu: {
            indication: 'Victime présentant des signes de détresse respiratoire',
            signes: [
                'Difficulté à respirer, essoufflement',
                'Respiration rapide ou lente',
                'Sueurs, agitation, angoisse',
                'Coloration bleutée des lèvres ou des extrémités',
                'Tirage (utilisation des muscles accessoires)'
            ],
            procedure: [
                {
                    titre: 'Position adaptée',
                    etapes: [
                        'Installer la victime dans la position où elle se sent le mieux',
                        'Généralement : position demi-assise ou assise',
                        'Desserrer les vêtements',
                        'Rassurer la victime'
                    ]
                },
                {
                    titre: 'Oxygénothérapie',
                    etapes: [
                        'Administrer de l\'oxygène en inhalation',
                        'Débit : 15 litres par minute chez l\'adulte (sauf avis médical)',
                        'Utiliser un masque haute concentration (MHC)',
                        'Concentration délivrée : 60 à 90%'
                    ]
                },
                {
                    titre: 'Surveillance',
                    etapes: [
                        'Surveiller la conscience',
                        'Surveiller la respiration',
                        'Mesurer la saturation en oxygène (SpO2) si possible',
                        'Objectif SpO2 : > 94%',
                        'Rassurer en permanence'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'En cas d\'arrêt respiratoire : débuter immédiatement la ventilation artificielle'
                },
                {
                    type: 'info',
                    texte: 'Si la victime a un traitement pour ses difficultés respiratoires, l\'aider à le prendre'
                }
            ]
        }
    },
    {
        id: 'pls',
        titre: 'Position Latérale de Sécurité (PLS)',
        categorie: 'techniques',
        icon: '🛌',
        description: 'Installation d\'une victime inconsciente qui respire en PLS',
        tags: ['inconscience', 'respiration', 'technique'],
        contenu: {
            indication: 'Victime inconsciente qui respire normalement',
            procedure: [
                {
                    titre: 'Préparation',
                    etapes: [
                        'Retirer les lunettes de la victime si elle en porte',
                        'Rapprocher les jambes côte à côte',
                        'Placer le bras proche de vous à angle droit, coude plié, paume vers le haut'
                    ]
                },
                {
                    titre: 'Retournement',
                    etapes: [
                        'Se placer à genoux à côté de la victime',
                        'Saisir l\'avant-bras opposé avec la main proche de sa tête',
                        'Placer le dos de sa main contre son oreille côté sauveteur',
                        'Maintenir fermement cette position',
                        'Avec l\'autre main, saisir la jambe opposée derrière le genou',
                        'Relever la jambe tout en gardant le pied au sol',
                        'Tirer sur la jambe pour faire pivoter la victime vers vous',
                        'Accompagner le mouvement jusqu\'à ce qu\'elle soit sur le côté'
                    ]
                },
                {
                    titre: 'Ajustements finaux',
                    etapes: [
                        'Ajuster la jambe du dessus : hanche et genou à angle droit',
                        'Ouvrir doucement la bouche de la victime',
                        'Vérifier que rien ne gêne la respiration'
                    ]
                },
                {
                    titre: 'Surveillance',
                    etapes: [
                        'Surveiller en permanence la respiration',
                        'Vérifier régulièrement que la bouche reste ouverte',
                        'Protéger du froid et de la chaleur',
                        'Attendre l\'arrivée des secours'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'En cas d\'arrêt de la respiration : replacer sur le dos et débuter la RCP'
                },
                {
                    type: 'info',
                    texte: 'Si le maintien en PLS dépasse 30 minutes, tourner la victime sur l\'autre côté'
                }
            ]
        }
    },

    // ==========================================
    // TRAUMATOLOGIE
    // ==========================================
    {
        id: 'traumatisme-crane',
        titre: 'Traumatisme crânien',
        categorie: 'traumatologie',
        icon: '🤕',
        description: 'Prise en charge d\'un traumatisme de la tête',
        tags: ['traumatisme', 'tête', 'surveillance'],
        contenu: {
            indication: 'Victime ayant reçu un choc à la tête',
            signes_gravite: [
                'Perte de connaissance même brève',
                'Amnésie de l\'accident',
                'Maux de tête violents',
                'Vomissements',
                'Troubles de la vision ou de l\'équilibre',
                'Somnolence anormale',
                'Écoulement de sang ou de liquide par le nez ou les oreilles',
                'Convulsions'
            ],
            procedure: [
                {
                    titre: 'Victime consciente',
                    etapes: [
                        'Maintenir la tête dans l\'axe du corps',
                        'Éviter tout mouvement de la nuque',
                        'Surveiller attentivement l\'état de conscience',
                        'Allonger la victime si possible',
                        'Alerter les secours si signes de gravité'
                    ]
                },
                {
                    titre: 'Victime inconsciente',
                    etapes: [
                        'Suspecter un traumatisme du rachis cervical',
                        'Maintenir la tête dans l\'axe',
                        'Libérer les voies aériennes',
                        'Si la victime respire : PLS adaptée (retournement à plusieurs)',
                        'Si la victime ne respire pas : RCP'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'Toujours suspecter une atteinte du rachis cervical en cas de traumatisme crânien'
                }
            ]
        }
    },
    {
        id: 'brulure',
        titre: 'Brûlure',
        categorie: 'traumatologie',
        icon: '🔥',
        description: 'Prise en charge d\'une brûlure thermique, chimique ou électrique',
        tags: ['brûlure', 'refroidissement', 'traumatisme'],
        contenu: {
            indication: 'Victime présentant une brûlure',
            procedure: [
                {
                    titre: 'Refroidissement immédiat',
                    etapes: [
                        'Arroser abondamment à l\'eau tempérée (15-25°C)',
                        'Durée : au moins 5 minutes',
                        'Continuer jusqu\'à l\'arrivée des secours si possible',
                        'Retirer les vêtements non adhérents pendant l\'arrosage',
                        'Ne jamais retirer les vêtements collés à la peau'
                    ]
                },
                {
                    titre: 'Protection de la brûlure',
                    etapes: [
                        'Recouvrir d\'un drap propre et sec',
                        'Ne rien appliquer sur la brûlure (ni crème, ni pommade)',
                        'Retirer bijoux et vêtements serrés avant apparition du gonflement'
                    ]
                },
                {
                    titre: 'Surveillance',
                    etapes: [
                        'Allonger la victime',
                        'Surveiller l\'état de conscience',
                        'Surveiller la respiration',
                        'Protéger du froid (hypothermie)',
                        'Rassurer'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'Les brûlures étendues, profondes, de la face, des mains, des articulations ou des parties génitales sont graves'
                },
                {
                    type: 'info',
                    texte: 'Pour les brûlures chimiques : arrosage prolongé (au moins 10 minutes)'
                }
            ]
        }
    },
    {
        id: 'plaie-grave',
        titre: 'Plaie grave',
        categorie: 'traumatologie',
        icon: '🩹',
        description: 'Prise en charge d\'une plaie grave',
        tags: ['plaie', 'traumatisme', 'pansement'],
        contenu: {
            indication: 'Plaie étendue, profonde, du thorax, de l\'abdomen, de l\'œil, ou avec corps étranger',
            procedure: [
                {
                    titre: 'Plaie avec corps étranger',
                    etapes: [
                        'Ne jamais retirer le corps étranger',
                        'Stabiliser l\'objet avec des pansements autour',
                        'Éviter tout mouvement de l\'objet',
                        'Alerter les secours'
                    ]
                },
                {
                    titre: 'Plaie du thorax',
                    etapes: [
                        'Installer la victime en position demi-assise',
                        'Laisser la plaie à l\'air libre',
                        'Surveiller la respiration',
                        'Administrer de l\'oxygène si détresse respiratoire'
                    ]
                },
                {
                    titre: 'Plaie de l\'abdomen',
                    etapes: [
                        'Allonger la victime, jambes repliées',
                        'Ne pas toucher aux organes sortis',
                        'Recouvrir d\'un pansement stérile humidifié',
                        'Rien par la bouche (ni boire ni manger)'
                    ]
                },
                {
                    titre: 'Plaie simple',
                    etapes: [
                        'Se laver les mains',
                        'Nettoyer la plaie à l\'eau et au savon',
                        'Rincer abondamment',
                        'Désinfecter',
                        'Protéger avec un pansement'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'Ne jamais retirer un corps étranger d\'une plaie'
                },
                {
                    type: 'info',
                    texte: 'Vérifier la vaccination antitétanique de la victime'
                }
            ]
        }
    },

    // ==========================================
    // MALAISES
    // ==========================================
    {
        id: 'malaise-general',
        titre: 'Malaise',
        categorie: 'malaises',
        icon: '😵',
        description: 'Prise en charge d\'une personne victime d\'un malaise',
        tags: ['malaise', 'surveillance', 'signes vitaux'],
        contenu: {
            indication: 'Personne se plaignant d\'un malaise ou présentant un comportement inhabituel',
            signes: [
                'Pâleur, sueurs',
                'Faiblesse, sensation de malaise',
                'Vertiges',
                'Douleur thoracique',
                'Difficulté à parler',
                'Troubles de la vision',
                'Maux de tête violents'
            ],
            procedure: [
                {
                    titre: 'Installation',
                    etapes: [
                        'Mettre la victime au repos dans la position où elle se sent le mieux',
                        'Généralement : allongée ou demi-assise',
                        'Desserrer les vêtements',
                        'Aérer'
                    ]
                },
                {
                    titre: 'Interrogatoire',
                    etapes: [
                        'Demander ce qu\'elle ressent',
                        'Demander si elle a des antécédents médicaux',
                        'Demander si elle suit un traitement',
                        'Demander si c\'est la première fois'
                    ]
                },
                {
                    titre: 'Aider la prise de médicament',
                    etapes: [
                        'Si la victime a un traitement prescrit pour ce type de malaise',
                        'L\'aider à le prendre',
                        'Ne jamais donner de médicament de sa propre initiative'
                    ]
                },
                {
                    titre: 'Surveillance et alerte',
                    etapes: [
                        'Surveiller l\'état de conscience',
                        'Surveiller la respiration',
                        'Alerter le 15 si : malaise persistant, signes de gravité, doute',
                        'Protéger du froid et de la chaleur'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'Signes de gravité : douleur thoracique, difficulté respiratoire, troubles de la parole, paralysie, convulsions → appeler le 15 immédiatement'
                }
            ]
        }
    },
    {
        id: 'avc',
        titre: 'Accident Vasculaire Cérébral (AVC)',
        categorie: 'malaises',
        icon: '🧠',
        description: 'Reconnaître et prendre en charge un AVC',
        tags: ['AVC', 'urgence', 'neurologique'],
        contenu: {
            indication: 'Suspicion d\'AVC - chaque minute compte !',
            signes: [
                'Déformation de la bouche',
                'Faiblesse ou paralysie d\'un bras',
                'Troubles de la parole',
                'Apparition brutale et soudaine',
                'Possibles : maux de tête violents, troubles de la vision, troubles de l\'équilibre'
            ],
            test_rapide: {
                titre: 'Test VITE (reconnaître un AVC)',
                etapes: [
                    'V - Visage : demander de sourire (bouche asymétrique ?)',
                    'I - Incapacité : lever les deux bras (un bras ne peut pas se lever ?)',
                    'T - Trouble de la parole : répéter une phrase simple (difficultés ?)',
                    'E - Extrême urgence : appeler le 15 immédiatement'
                ]
            },
            procedure: [
                {
                    titre: 'Alerte immédiate',
                    etapes: [
                        'Appeler le 15 en urgence',
                        'Noter l\'heure de début des symptômes',
                        'Cette information est capitale pour le traitement'
                    ]
                },
                {
                    titre: 'Installation',
                    etapes: [
                        'Allonger la victime, tête et épaules légèrement surélevées',
                        'Desserrer les vêtements',
                        'Rien par la bouche (ni boire ni manger)'
                    ]
                },
                {
                    titre: 'Surveillance',
                    etapes: [
                        'Surveiller l\'état de conscience',
                        'Surveiller la respiration',
                        'Rassurer',
                        'Si inconscient mais respire : PLS',
                        'Si ne respire pas : RCP'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'AVC = URGENCE ABSOLUE. Chaque minute compte pour limiter les séquelles. Appeler le 15 immédiatement.'
                },
                {
                    type: 'info',
                    texte: 'Noter l\'heure de début des symptômes est crucial pour le traitement médical'
                }
            ]
        }
    },
    {
        id: 'crise-convulsive',
        titre: 'Crise convulsive',
        categorie: 'malaises',
        icon: '⚡',
        description: 'Conduite à tenir face à une crise convulsive',
        tags: ['convulsions', 'épilepsie', 'protection'],
        contenu: {
            indication: 'Personne présentant des convulsions (mouvements brusques et involontaires)',
            procedure: [
                {
                    titre: 'Pendant la crise',
                    etapes: [
                        'Protéger la victime : écarter les objets dangereux',
                        'Glisser quelque chose de mou sous la tête',
                        'Ne rien mettre dans la bouche',
                        'Ne pas maintenir la victime',
                        'Desserrer les vêtements',
                        'Noter l\'heure de début de la crise'
                    ]
                },
                {
                    titre: 'Après la crise',
                    etapes: [
                        'La victime est généralement inconsciente',
                        'Vérifier la respiration',
                        'Si elle respire : installer en PLS',
                        'Surveiller jusqu\'à réveil complet',
                        'Rassurer au réveil (désorientation fréquente)'
                    ]
                },
                {
                    titre: 'Alerte',
                    etapes: [
                        'Appeler le 15 si :',
                        '- Première crise',
                        '- Crise de plus de 5 minutes',
                        '- Crises répétées',
                        '- Traumatisme pendant la crise',
                        '- Grossesse',
                        '- Ne reprend pas connaissance'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'Ne jamais essayer d\'ouvrir la bouche ou d\'y introduire un objet pendant la crise'
                },
                {
                    type: 'info',
                    texte: 'Une personne épileptique connue peut ne pas nécessiter d\'alerte si la crise est brève et habituelle'
                }
            ]
        }
    },

    // ==========================================
    // SITUATIONS PARTICULIÈRES
    // ==========================================
    {
        id: 'obstruction-voies-aeriennes',
        titre: 'Obstruction des voies aériennes',
        categorie: 'situations-particulieres',
        icon: '🫁',
        description: 'Étouffement - claques dans le dos et compressions abdominales',
        tags: ['étouffement', 'Heimlich', 'urgence'],
        contenu: {
            indication: 'Personne qui s\'étoufffe (aliment, objet coincé dans la gorge)',
            signes: [
                'Impossibilité de parler',
                'Impossibilité de tousser efficacement',
                'Impossibilité de respirer',
                'Souvent : mains portées à la gorge',
                'Agitation, angoisse',
                'Coloration bleutée'
            ],
            procedure: [
                {
                    titre: 'Obstruction totale - Victime consciente',
                    etapes: [
                        '1. Donner 5 claques dans le dos',
                        '2. Si inefficace : 5 compressions abdominales (méthode de Heimlich)',
                        '3. Alterner 5 claques / 5 compressions',
                        '4. Continuer jusqu\'à désobstruction ou perte de connaissance'
                    ]
                },
                {
                    titre: 'Claques dans le dos',
                    etapes: [
                        'Se placer sur le côté, légèrement en arrière',
                        'Soutenir le thorax avec une main',
                        'Pencher la victime en avant',
                        'Donner 5 claques vigoureuses entre les omoplates',
                        'Vérifier après chaque claque si l\'objet est expulsé'
                    ]
                },
                {
                    titre: 'Compressions abdominales (Heimlich)',
                    etapes: [
                        'Se placer derrière la victime',
                        'Mettre les bras sous ses aisselles',
                        'Placer un poing fermé entre nombril et sternum',
                        'Placer l\'autre main par-dessus',
                        'Tirer franchement vers soi et vers le haut',
                        'Effectuer 5 compressions'
                    ]
                },
                {
                    titre: 'Si la victime perd connaissance',
                    etapes: [
                        'Accompagner la chute au sol',
                        'Alerter le 15',
                        'Débuter immédiatement la RCP (30 compressions / 2 insufflations)',
                        'Avant chaque insufflation : vérifier si l\'objet est visible et accessible',
                        'Si visible et accessible : le retirer avec les doigts'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'Ne jamais effectuer de compressions abdominales chez la femme enceinte ou le nourrisson'
                },
                {
                    type: 'info',
                    texte: 'Toute personne ayant reçu des compressions abdominales doit consulter un médecin'
                }
            ]
        }
    },
    {
        id: 'noyade',
        titre: 'Noyade',
        categorie: 'situations-particulieres',
        icon: '🌊',
        description: 'Prise en charge d\'une victime de noyade',
        tags: ['noyade', 'eau', 'réanimation'],
        contenu: {
            indication: 'Personne retirée de l\'eau en détresse',
            procedure: [
                {
                    titre: 'Sécurité',
                    etapes: [
                        'S\'assurer de sa propre sécurité avant d\'intervenir',
                        'Utiliser du matériel de sauvetage si possible',
                        'Ne pas se mettre en danger'
                    ]
                },
                {
                    titre: 'Victime consciente',
                    etapes: [
                        'Sortir la victime de l\'eau',
                        'Allonger, réchauffer (couverture)',
                        'Administrer de l\'oxygène si disponible',
                        'Surveiller attentivement',
                        'Alerter le 15 (toute noyade nécessite un avis médical)'
                    ]
                },
                {
                    titre: 'Victime inconsciente qui respire',
                    etapes: [
                        'Sortir de l\'eau le plus rapidement possible',
                        'Installer en PLS',
                        'Sécher et réchauffer',
                        'Alerter le 15',
                        'Surveiller la respiration en permanence'
                    ]
                },
                {
                    titre: 'Victime inconsciente qui ne respire pas',
                    etapes: [
                        'Commencer 5 insufflations dès que possible (dans l\'eau si sauveteur formé)',
                        'Sortir de l\'eau rapidement',
                        'Alerter le 15',
                        'Débuter immédiatement la RCP',
                        'Installer le DAE dès disponible',
                        'Sécher le thorax avant de coller les électrodes'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'Toute victime de noyade, même si elle semble aller bien, doit être examinée par un médecin'
                },
                {
                    type: 'info',
                    texte: 'Privilégier les insufflations en premier chez une victime de noyade (origine respiratoire)'
                }
            ]
        }
    },
    {
        id: 'hypothermie',
        titre: 'Hypothermie',
        categorie: 'situations-particulieres',
        icon: '🥶',
        description: 'Prise en charge d\'une victime en hypothermie',
        tags: ['froid', 'température', 'réchauffement'],
        contenu: {
            indication: 'Personne ayant été exposée au froid',
            signes: [
                'Frissons intenses',
                'Peau froide et pâle',
                'Comportement inhabituel, confusion',
                'Maladresse, troubles de l\'équilibre',
                'Ralentissement progressif'
            ],
            signes_gravite: [
                'Arrêt des frissons',
                'Troubles de conscience',
                'Rigidité musculaire',
                'Respiration lente',
                'Pouls lent ou difficile à percevoir'
            ],
            procedure: [
                {
                    titre: 'Isoler du froid',
                    etapes: [
                        'Mettre la victime à l\'abri',
                        'Retirer les vêtements mouillés',
                        'Envelopper dans une couverture',
                        'Couvrir la tête',
                        'Isoler du sol'
                    ]
                },
                {
                    titre: 'Réchauffement',
                    etapes: [
                        'Réchauffer progressivement',
                        'Couverture de survie (doré vers la victime)',
                        'Boisson chaude sucrée si victime consciente',
                        'Ne pas frotter la peau',
                        'Ne pas réchauffer trop vite (risque)'
                    ]
                },
                {
                    titre: 'Surveillance et alerte',
                    etapes: [
                        'Surveiller l\'état de conscience',
                        'Surveiller la respiration',
                        'Alerter le 15 si signes de gravité',
                        'Si inconsciente : PLS si elle respire, RCP si elle ne respire pas'
                    ]
                }
            ],
            alertes: [
                {
                    type: 'danger',
                    texte: 'Une hypothermie grave peut rendre le pouls et la respiration très difficiles à percevoir. Rechercher attentivement avant de débuter la RCP.'
                },
                {
                    type: 'info',
                    texte: 'Rien par la bouche si la victime est inconsciente ou présente des troubles de la conscience'
                }
            ]
        }
    }
];

// Catégories pour la navigation
const categories = {
    'urgences-vitales': {
        nom: 'Urgences Vitales',
        icon: '🚨',
        couleur: 'urgence'
    },
    'traumatologie': {
        nom: 'Traumatologie',
        icon: '🩹',
        couleur: 'trauma'
    },
    'malaises': {
        nom: 'Malaises',
        icon: '😵',
        couleur: 'malaise'
    },
    'techniques': {
        nom: 'Gestes Techniques',
        icon: '⚕️',
        couleur: 'technique'
    },
    'pediatrie': {
        nom: 'Pédiatrie',
        icon: '👶',
        couleur: 'pediatrie'
    },
    'situations-particulieres': {
        nom: 'Situations Particulières',
        icon: '📋',
        couleur: 'situation'
    }
};
