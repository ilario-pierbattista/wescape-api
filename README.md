Wescape RESTful service
=======================

# Current Status
* master [![Build Status](https://travis-ci.org/ilario-pierbattista/wescape-api.svg?branch=master)](https://travis-ci.org/ilario-pierbattista/wescape-api)
* dev [![Build Status](https://travis-ci.org/ilario-pierbattista/wescape-api.svg?branch=dev)](https://travis-ci.org/ilario-pierbattista/wescape-api)

[Wescape](https://github.com/ilario-pierbattista/wescape-android) is an indoor navigation app that will guide inside buildings in both normal and emergency situations.
* Use the step-by-step navigator to reach a zone of the building
* Let you guide through the nearest emergency exit in an emergency situation
* Scan the QR code inside the building to know exactly where you are
* Only the the fastest and secure routing thanks to the communication system with the sensors inside the buildings

Wescape is the final project developed during the course of Software Engineering at UnivPM mainly by Ilario Pierbattista and Vittorio Morganti, with the help of Melvin Mancini and Giuseppe Albanese, under the supervision of Gabriele Bernardini, Silvia Santarelli, Luca Spalazzi.
Wescape was born from an idea of some researchers at UnivPM: Gabriele Bernardini, Marco D'Orazio, Enrico Quagliarini, Silvia Santarelli, Luca Spalazzi.

# Panoramica delle tecnologie utilizzate
Il servizio è scritto in *PHP* utilizzando il framework *Symfony*, che aggrega le funzionalità offerte dai vari *Bundle* sviluppati dalla comunity.

Sono inclusi anche parecchi script per l'estrazione e la manipolazione dei dati, nonchè per la configurazione semi automatizzata del server e per il popolamento del database,
Tali script sono scritti in *javascript*, eseguibili all'interno dell'ambiente *Node.js*.

È fornita, inoltre, un'estensiva suite di test funzionali, scritti appositamente per il collaudo delle API offerte.

