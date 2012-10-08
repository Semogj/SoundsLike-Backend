function lastfm_fetchInfoEvent(event){
                
    var button = $('#btLastfmUsername').attr('disabled', 'true');
    var username = $.trim($('#txtLastfmUsername').val());
    //empty username
    if(username.length == 0){
        setInfo("Please input a valid Last.fm user!", 'error');
        return;
    }
                
    var nTopArtists = $('#topArtistsNumber').val();
    var minSimilarityVal = $('#minSimilarity').val() / 100;
    var maxNeighbors = $('#maxNeighbors').val();
                
    setInfo("Extracting " + username + " top artists...");
    try{
        lastfm.user.getTopArtists({
            user: username, 
            period:'overall', 
            limit: nTopArtists
        }, 

        {
            success: processTopArtists, 
            error: lastfmError
        });
    }catch(ex){
        setInfo(ex.message, 'error');    
    }
    function processTopArtists(data){
        //console.log(data);
                
        var topArtistsArr = data.topartists.artist;
  
        var cacheKey = username + "topArtists" + topArtistsArr.length;
                    
        var nodeCache = cacheGet(cacheKey + "_nodes");
        var linkCache = cacheGet(cacheKey + "_link");
        if(isset(nodeCache) && isset(linkCache)){
            console.log("Cached values found!")
            updateVisualization(nodeCache, linkCache);
            return;
        }else{
            console.log("No cached values are available...")
        }
                    
        var taSize = topArtistsArr.length;
                
        for(var i = 0; i < taSize; i++){
            topArtistsArr[i].nodeGroup = 0; 
        }            
        //nodes
        var artistsArr = new Array().concat(topArtistsArr);
        //links
        var relationsArray = new Array();  
                
        var taIndex = 0;
        var currentTaArtist = topArtistsArr[taIndex];
                
        var perc = Math.round(100 * taIndex / taSize); 
        setInfo("("+ perc +" %) Fetching " + currentTaArtist.name + " related artists...");
        lastfm.artist.getSimilar({
            artist: currentTaArtist.name , 
            mbid: currentTaArtist.mbid, 
            limit: maxNeighbors
        }, 

        {
            success: processSimilarArtists, 
            error: lastfmError
        });
                               
        function processSimilarArtists(data){
            var perc = Math.round(100 * taIndex / taSize); 
            setInfo("("+ perc +" %) Processing " + currentTaArtist.name + " related artists...");
            var similarArtistArr = data.similarartists.artist;
            var similarSize = similarArtistArr.length;
            for(var i = 0; i < similarSize; i++){
                if(similarArtistArr[i].match >= minSimilarityVal){
                    var index = insertIntoArtistSet(artistsArr, similarArtistArr[i]);
                    insertIntoLinkSet(relationsArray, {
                        source: taIndex, 
                        target: index, 
                        value: similarArtistArr[i].match
                        });
                }
            }
            //recursive call for getting top artist similar
            taIndex++;
            currentTaArtist = topArtistsArr[taIndex];
            if(taIndex < taSize){
                var perc = Math.round(100 * taIndex / taSize); 
                setInfo("("+ perc +" %) Fetching " + currentTaArtist.name + " related artists...");
                lastfm.artist.getSimilar({
                    artist: currentTaArtist.name , 
                    mbid: currentTaArtist.mbid, 
                    limit: settings.defaultSimilarArtists
                    }, 

                    {
                    success: processSimilarArtists, 
                    error: lastfmError
                }); 
            }else{
                cacheStore(cacheKey + "_nodes", artistsArr);
                cacheStore(cacheKey + "_links", relationsArray);
                updateVisualization(artistsArr, relationsArray);
            }      
        }
    }
            
            
    function lastfmError(code, message){ 
        setInfo(message, 'error');
        $('#btLastfmUsername').removeAttr('disabled');
    }
            
}
            
/**
             * Add an artist to a specific set and returns the position in the array.
             * If the artist exists, returns the position, if not inserts the artist
             *  into the array and returns the position.
             */
function insertIntoArtistSet(artistArray, lastFmArtist){
    var size = artistArray.length;
    if(isset(lastFmArtist.mbid)){
        for(var i = 0; i < size; i++){
            if(artistArray[i].name == lastFmArtist.name && artistArray[i].mbid == lastFmArtist.mbid){
                return i;
            }
        }
    }else{
        for(var i = 0; i < size; i++){
            if(artistArray[i].name == lastFmArtist.name){
                return i;
            }
        }
    }  
    artistArray.push({
        name: lastFmArtist.name, 
        mbid: isset(lastFmArtist.mbid) ? lastFmArtist.mbid : null,
        url: isset(lastFmArtist.url) ? lastFmArtist.url : ''
    });
    return size
}
function insertIntoLinkSet(linkArray, newLink, maxVal){
    var size = linkArray.length;
    for(var i = 0; i < size; i++){
        var l = linkArray[i];
        if((l.source == newLink.source && l.target == newLink.target) 
            || (l.target == newLink.source && l.source == newLink.target) )
            return false;
    }
    linkArray.push(newLink);
    return true;
}
            
function outputArtistNames(artistArr){
    for(var i = 0; i < artistArr.length; i++)
        $('#output').append(artistArr[i].name + "<br />");
}