

var soundEventObj = null
var lastClickEventTime = 0;
function VIRUS_testData1Event(){
    var result = VIRUS_convertSimilarSounds(VIRUS_getSimilarSoundsEvents(TEST_SOUND_EVENT_ID));
    var nodes = result[0];
    var links = result[1];
    
    setInfo(nodes.length + ' nodes and '+ links.length + ' connection. Rendering visualization...');
    
    console.log(nodes);
    console.log(links);
    $('#btLastfmUsername').removeAttr('disabled');
  
    var config = {
        width: 1024, 
        height: 540, 
        gravity: 0.04, 
        defaultRadius: 30, 
        charge:-100,  
        linkMinimumDistance:15, 
        linkMaximumDistance: 300, 
        bordersLimit: $('#btBordersLimit').val() === "Border Limit : On" ? true : false,
        nodeFillScale: d3.scale.ordinal().range(["#AEC7E8","#C00000"])
    }    
    graphObj = new ForceGraphVis.create("#chart", config);
    
    graphObj.onNodeClick = function(node, index){
        var selector = d3.select(this).select('.text');
        
        var now = Date.now();
        if(now - lastClickEventTime < 500) //ja passou 1 segundo desde o ultimo evento?
        {
            return;
        }
        lastClickEventTime = now;
        var tmp = node.name.split(".");
        
        //FIXME: fix possible errors here
        var movieName = tmp[0];
        for(var i = 1; i < tmp.length - 1; i++)
            movieName += "." + tmp[i]
        
        var soundPath = "sounds/movies/" + movieName + "/" + node.name; 
       
        //global var soundEventObj, we only want 1 sound playing at a time
        if(isset(soundEventObj)){
            soundEventObj.stop();
        }
        soundEventObj = new buzz.sound(soundPath, {
        //var mySound = new buzz.sound( "sounds/effects/Alien 1", {
            formats: [ "wav", "mp3"]
        });
	selector.text("Playing: " + node.name);
        var stopEvent = function(e) {
            selector.text(node.name);
        };        
        soundEventObj.setVolume(40).play().bind( "ended", stopEvent);
        
        
    }
    graphObj.onNodeMouseOver = function(node, index){
        console.log("MouseOver event over node[" + index + "] \"" +  node.name + "\"");
        var newRadius = node.radius * 1.2;
        var difRadius = (newRadius - node.radius) * -1;        
        var selector = d3.select(this);
        selector.style('z-index', Math.round(selector.style('z-index') * 2))
        .transition().duration(250)
        .style('margin-top', difRadius + "px")
        .style('margin-left', difRadius + "px")
        .style('width', newRadius * 2 + "px")
        .style('height', newRadius * 2  + "px")
        .style('border-radius', newRadius + "px");
        
    //o.layout.resume();
    }
    graphObj.onNodeMouseOut = function(node, index){
        console.log("MouseOut event over node[" + index + "] \"" +  node.name + "\"");      
        var selector = d3.select(this);
        //selector.select('.text').text(node.name);   
        selector.style('z-index', Math.round(selector.style('z-index') / 2))
        .transition().duration(250)
        .style('margin-top',"0px")
        .style('margin-left', "0px")
        .style('width', node.radius * 2 + "px")
        .style('height', node.radius * 2 + "px")
        .style('border-radius', node.radius + "px");
        graphObj.layout.resume();
    }
    
    
    graphObj.start(nodes, links);
  
}
var TEST_SOUND_EVENT_ID = "24.S01E01.460";

function VIRUS_getSimilarSoundsEvents(eventId){
    if(eventId == "24.S01E01.460")
        return [
        {
            source:"24.S01E01.460", 
            target: "24.S01E01.893", 
            value:14.35
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.896", 
            value:52.02
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.459", 
            value:53.5
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.892", 
            value:65.05
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.464", 
            value:68.13
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.894", 
            value:69.44
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.895", 
            value:72.47
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.1209", 
            value:72.62
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.1230", 
            value:73.8
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.124", 
            value:80.06
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.512", 
            value:94.35
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.444", 
            value:95.02
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.15", 
            value:103.5
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.121", 
            value:165.05
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.656", 
            value:168.13
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.878", 
            value:169.44
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.443", 
            value:172.47
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.111", 
            value:172.62
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.214", 
            value:173.8
        },

        {
            source:"24.S01E01.460", 
            target: "24.S01E01.658", 
            value:180.06
        }
        ];
    else 
        return [];
}

function VIRUS_getSoundEvent(eventId){
    return {
        name: eventId
    }; 
}

function VIRUS_convertSimilarSounds(similarArr){
    var nodeArr = [];
    var maxValue = 0;
    var i, s = similarArr.length;
    //find the maximum value for normalization
    for(i = 0; i < s; i++){
        if(similarArr[i].value > maxValue)
            maxValue = similarArr[i].value;
    }
    var normScale = d3.scale.linear().domain([0, maxValue]).range([1.0,0.0]);
  
    for(i = 0; i < s; i++){
        similarArr[i].source = VIRUS_insertIntoNodeSet(nodeArr, VIRUS_getSoundEvent(similarArr[i].source));
        similarArr[i].target = VIRUS_insertIntoNodeSet(nodeArr, VIRUS_getSoundEvent(similarArr[i].target));
        similarArr[i].value = normScale(similarArr[i].value);
    }
    return [nodeArr, similarArr];   
}



/**
             * Add an node to a specific set and returns the position in the array.
             * If the artist exists, returns the position, if not inserts the node
             *  into the array and returns the position.
             */
function VIRUS_insertIntoNodeSet(nodeArray, node){
    var size = nodeArray.length;
    for(var i = 0; i < size; i++){
        if(nodeArray[i].name == node.name){
            return i;
        }
    }
   
    nodeArray.push({
        name: node.name 
    });
    return size
}
function VIRUS_insertIntoLinkSet(linkArray, newLink){
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
