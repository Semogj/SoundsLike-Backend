SoundsLike-Virus-Webservice
===========================

The PHP webservice associated to the VIRUS project (http://virus.di.fc.ul.pt) and used by SoundsLike prototype.

### For more information and install instructions, go [here](https://github.com/Semogj/SoundsLike-Frontend/)


## Audio Similarity DB

You will find a directory named "Audio Similarity DB" which contains the sql structure and data used for buiding and testing the SoundsLike prototype.

Inside there is a "sql" folder with the database sql files. I used MySQL with InnoDB as database engine, but MariaDB is also recommended. 

The remaining files are the "Entity–relationship" model for the current database and a suggestion for a new version (v2) which was never implemented.

The database includes similarity data for the movie "Back To The Future". You can find and download the used version here:
https://mega.co.nz/#F!Y9xFAJSI!f6d2HCVOOqQvMJMZZvkfjg

## SoundsLike

SoundsLike is a prototype which is integrated directly as a part of MovieClouds for the purpose of classifying and browsing movies’ soundtracks.

It provides an interface for interactive navigation and labelling of audio excerpts, integrating game elements to induce and support users to contribute for this task, by allowing and simplifying listening to the audio on the context of the movie and presenting similar audios and suggesting labels.

This repository contains the webservice that powers the front-end with the required movie's data.

For SoundsLike, please, go [here](https://github.com/Semogj/SoundsLike-Frontend/)
