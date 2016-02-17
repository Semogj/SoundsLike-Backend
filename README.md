#SoundsLike-Virus-Webservice

SoundsLike presents as an web application for audio classification game for movies, associated to the VIRUS project.

This repository holds the backend solution for Soundslike, containing the PHP webservice and files for the creation of a MySQL database.

### For information about the project, including install instructions, please [visit the SoundsLike Frontend repository](https://github.com/Semogj/SoundsLike-Frontend/)

## Used technology and libraries

The following technology were used to implement the backend:


## Audio Similarity Database

You will find a directory named "Audio Similarity DB" which contains the sql structure and data used for buiding and testing the SoundsLike prototype.

Inside the folder, you will find a "sql" folder, and ".mwb" files:

The sql folder contains all the database sql files. MySQL with InnoDB as database engine is recommended. MariaDB may also work as alternative Data Base Management System. 

The ".mwb" files are [MySQL Workbench](https://www.mysql.com/products/workbench/) source files containing the "Entity–relationship" model for the database, and are paired with a png image file with the same name:

- "VIRUS Audio Similarity DB" - The diagram for the current database
- "VIRUS Audio Similarity DB v2.mwb" - A diagram suggesting a newer and improved version, which was never implemented.

The database files includes the similarity data for the movie "Back To The Future". This data is the result of running an audio comparison tool developed in a context of a previous VIRUS project, not included in this repository. The movie was divided in 4 seconds audio pieces, then each piece was compared with others.

A similarity entry represents the difference between two different audio excertps. This difference is translated to a decimal value, whera 0.0 means the same value and the value raises as long the audio sounds different in the ears of a user.

The reason behind the use of the movie "Back to the Future" was due to be one of the project coordinator favourite movie. We always try to appeal our coordinators and bosses ;).

Due to copyright purposes, I am not authorised to upload the used movie file to Github. But hey, since it's all in the name of science, you can download the used version here: https://mega.co.nz/#F!Y9xFAJSI!f6d2HCVOOqQvMJMZZvkfjg - Please use this version for non-profit, research and educational purposes.

## SoundsLike

SoundsLike is a prototype which is integrated directly as a part of MovieClouds for the purpose of classifying and browsing movies’ soundtracks.

It provides an interface for interactive navigation and labelling of audio excerpts, integrating game elements to induce and support users to contribute for this task, by allowing and simplifying listening to the audio on the context of the movie and presenting similar audios and suggesting labels.

This repository contains the webservice that powers the front-end with the required movie's data.

For SoundsLike, please, go [here](https://github.com/Semogj/SoundsLike-Frontend/)
