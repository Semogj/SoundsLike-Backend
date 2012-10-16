function create_force_visualization(pageElemSelector, config){
    //create an object to return that will hold the layout, properties and the visualization
    var o = {};
    processConfig(o, config);
    
    //set up the layout (the physics context)
    o.layout = d3.layout.force()
    .gravity(o.gravity)
    .charge(o.charge)
    .linkDistance(function (link){
        var sourceRadius = isset(link.source.radius) ? link.source.radius : o.radius;
        var targetRadius = isset(link.target.radius) ? link.target.radius : o.radius;
        var lengthMult = issetDefault(link.lengthMult, 1);
        if(isset(link.value)){
            //we are assuming that link.value have normalized values between 0 and 1.
            var value = link.value < 0 ? 0 : link.value > 1 ? 1 : link.value;
            return (sourceRadius + targetRadius + o.minLinkDistance + o.maxLinkDistance - (o.maxLinkDistance - o.minLinkDistance) * value) * lengthMult;
        }else{
            return (sourceRadius + targetRadius + o.defaultLinkDistance)*lengthMult;
        }

    })
    .size([o.width,o.height]);
    
    //element cleanup
    $(pageElemSelector).html("");
          
    //set up the visualization
    o.canvas = d3.select(pageElemSelector).style("width", o.width + "px").style("height", o.height + "px");
                
    //NOTE: similar to the 3D ambients, we have two contextes: the physics context and the visualization context
    //The physics context is were the elements directions are interpolated and position calculated.
    //The visualization context is what we going to show to the user (in the HTML!)
    //The physics context updates the visualization context, but both need to be set!
    //
    //visualization context = canvas (o.canvas)
    //physics context = layout (o.layout)
    
    //maping some functions
    o.start = function(){
        o.layout.start();
        return o;
    }
    o.stop = function(){
        o.layout.stop();
        return o;
    }
    
    o.tick = o.stop = function(){
        o.layout.tick();
        return o;
    }
    /**
     * Similar to o.stop(); o.tick(); o.start();
     */
    o.update = function(){
        o.layout.start();
        o.layout.tick();
        o.layout.start();
        return o;
    }
    o.changeZoom = function (position, value){
        o.zoom = value;
        var transformStr = "translate(" +  (position[0] + ((o.width - (o.width * o.initialZoom))/2)) + ',' + (position[1] + ((o.height - (o.height * o.initialZoom))/2)) + ")scale(" + value*o.initialZoom + ")";
        o.canvas.style("-webkit-transform", transformStr)
        .style("-moz-transform", transformStr)
        .style("-ms-transform", transformStr)
        .style("-o-transform", transformStr)
        .style("transform", transformStr);
        return o;
    }
    return o;
    
    function processConfig(o, config){
        o.width = getProperty(config, "width", 960);
        o.height = getProperty(config, "height", 500);
        o.gravity = getProperty(config, "gravity", 0.05);
        o.charge = getProperty(config, "charge", -100);
        o.radius = getProperty(config, "radius", 6);
        o.defaultLinkDistance = getProperty(config, "defaultLinkDistance", 30);
        o.minLinkDistance = getProperty(config, "minLinkDistance", 10);
        o.maxLinkDistance = getProperty(config, "maxLinkDistance", 60);
        o.bordersLimit = getProperty(config, "bordersLimit", true);
        o.fill = d3.scale.category20();
        o.pzoom  = o.zoom = getProperty(config, "zoom", 1);
        o.minZoom = getProperty(config, "minZoom", 0.0625); //-4x
        o.maxZoom = getProperty(config, "maxZoom", 5); //5x
        o.initialZoom = getProperty(config, "initialZoom", o.zoom);
    //        o.nodeZindex = getProperty(config, "nodeZindex", 500);
    }
}

            
function init_visualization(graphObj, nodes, links){
    var linkColorScale = d3.scale.linear().domain([0, 0.125, 0.25, 0.375, 0.5, 0.625, 0.75, 0.875, 1])
    .range(["#0066FF", "#00CC99", "#00CC00", "#99CC00", "#FF9900", "#FF6600", "#FF3300", "#FF0000", "#B20000"]);
    
    var o = graphObj;
    //add links to the visualization
    o.visLinks = o.canvas.selectAll("div.link")
    .data(links)
    .enter()
    .append("div")
    .attr("class", "link")
    //link thickness
    .style("z-index",function(link){
        if(isset(link.value)){
            return Math.round(link.value * 100);
        }else{
            return 1;
        }
    })
    .style("background-color", function(link){
        if(isset(link.value)){
            //round it to 2 decimals
            var value = Math.round(link.value * 100) / 100;
            //we are assuming that link.value have normalized values between 0 and 1.
            value = value < 0 ? 0 : value > 1 ? 1 : value;
            return linkColorScale(value); 
        }else{
            return "#000000";
        }
        
    })
    
                
    cleanupNodes(nodes);
    //add nodes to the visualization
    o.visNodes =  o.canvas.selectAll("div.node")
    .data(nodes)
    .enter()
    .append("div")
    .attr("class", function(node){
        var ret = "node";
        ret += isset(node.nodeGroup) ? " group_" + node.nodeGroup : "";
        return ret;
    })
    .html(function(node){
        return '<div class="text" >' + node.name + '</div>';
    })
    .style("width", function(d) { 
        return (isset(d.radius) ? d.radius*2 : o.radius*2) + "px"; 
    })
    .style("height", function(d) { 
        return (isset(d.radius) ? d.radius*2 : o.radius*2) + "px"; 
    })
    .style("border-radius", function(d) { 
        return (isset(d.radius) ? d.radius : o.radius) + "px"; 
    })
    .style("background", function(d) {
        var nodeGroup = issetDefault(d.nodeGroup, 1);
        return o.fill(nodeGroup);
    })
    .style("border-color", function(d) {
        var nodeGroup = issetDefault(d.nodeGroup, 1);
        return d3.rgb(o.fill(nodeGroup)).darker();
    })
    //    .style('z-index', function(d){
    //        return issetDefault(d.zindex, o.nodeZindex);
    //    })
    .on('click', onNodeClick)
    .on('mouseover', onNodeMouseOver)
    .on('mouseout', onNodeMouseOut)
    //    .call(d3.behavior.drag()
    //        .on('dragstart', dragStart)
    //        .on('drag', dragMove)
    //        .on('dragend', dragEnd) 
    //    );
    .call(o.layout.drag)
    .call(d3.behavior.zoom().on("zoom", function(){
        //Zoom work in progress
        
        var translatePos = d3.event.translate;
        var value = o.zoom;
 
        //detect the mousewheel event, then subtract/add a constant to the zoom level and transform it
        if (d3.event.sourceEvent.type=='mousewheel' || d3.event.sourceEvent.type=='DOMMouseScroll'){
            if (d3.event.sourceEvent.wheelDelta){
                if (d3.event.sourceEvent.wheelDelta > 0){
                    value = value + 0.1;
                }else{
                    value = value - 0.1;
                }
            }else{
                if (d3.event.sourceEvent.detail > 0){
                    value = value + 0.1;
                }else{
                    value = value - 0.1;
                }
            }
            o.changeZoom(translatePos, value);
            
        //o.update();
        }
        
    //transformVis(d3.event.translate, value);
    }));
    
    //tell the physics how to update and to start
    o.layout
    .nodes(nodes)
    .links(links)
    .on("tick", tick)
    .start();
    
    function tick() {
        o.visNodes.style("left", function(d) {
            var pos = 0.0;
            if(o.bordersLimit || d.bordersLimit)
                pos = (d.x = Math.max(0, Math.min(o.width - d.radius * 2, d.x)));  //x bondaries limit
            else
                pos = d.x;
            return  pos + "px";
        })
        .style("top", function(d) {
            var pos = 0.0;
            if(o.bordersLimit || d.bordersLimit) 
                pos = (d.y = Math.max(0, Math.min(o.height - d.radius * 2, d.y))); //y bondaries limit
            else
                pos = d.y;
            return  pos + "px";
        });

        o.visLinks.style("left", function(d) {
            return (d.source.x + d.source.radius)  + "px"; 
        })
        .style("top", function(d) {
            return (d.source.y + d.source.radius)  + "px"; 
        })
        .style("width", length)
        .style("-webkit-transform", transform)
        .style("-moz-transform", transform)
        .style("-ms-transform", transform)
        .style("-o-transform", transform)
        .style("transform", transform);
    };

    function transform(d) {
        return "rotate(" + Math.atan2(
            (d.target.y + d.target.radius) - (d.source.y+ d.source.radius), 
            (d.target.x + d.target.radius) - (d.source.x + d.source.radius)
            ) * 180 / Math.PI + "deg)";
    }

    function length(d) 
    {
        var dx = (d.target.x + d.target.radius) - (d.source.x + d.source.radius),
        dy = (d.target.y + d.target.radius) - (d.source.y + d.source.radius);
        return Math.sqrt(dx * dx + dy * dy) + "px";
    }
    function dragStart(d, i){
        o.layout.stop()
    }
    function dragMove(d, i) {
        d.px += d3.event.dx;
        d.py += d3.event.dy;
        d.x += d3.event.dx;
        d.y += d3.event.dy; 
        tick(); // this is the key to make it work together with updating both px,py,x,y on d !
    }
    function dragEnd(d, i){
        o.layout.start()
    }
    
    function onNodeMouseOver(node, index){
        console.log("MouseOver event over node[" + index + "] \"" +  node.name + "\"");
        var newRadius = node.radius * 1.5;
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
    function onNodeMouseOut(node, index){
        console.log("MouseOut event over node[" + index + "] \"" +  node.name + "\"");
        
        var selector = d3.select(this);
        
        selector.select('.text').text(node.name);
        
        selector.style('z-index', Math.round(selector.style('z-index') / 2))
        .transition().duration(250)
        .style('margin-top',"0px")
        .style('margin-left', "0px")
        .style('width', node.radius * 2 + "px")
        .style('height', node.radius * 2 + "px")
        .style('border-radius', node.radius + "px");
        o.layout.resume();
    }
    function onNodeClick(node, index){
        console.log("MouseClick event over node[" + index + "] \"" +  node.name + "\"");
        var selector = d3.select(this).select('.text');
        selector.transition().duration(500).text('CLICK!').each('end', function(){
            selector.text(node.name);
        });
    }
    function cleanupNodes(nodeArr){
        var size = nodeArr.length;
        for(var i = 0; i < size; i++)
        {
            if(issetDefault(nodeArr[i].nodeGroup, -1) == 0){
                nodeArr[i].radius = o.radius * 1.5;
            }else
            if(!isset(nodeArr[i].radius)){ //default radius check
                nodeArr[i].radius = o.radius;
            }
            if(isset(nodeArr[i].radiusMult)){
                nodeArr[i].radius *= nodeArr[i].radiusMult;
            }else{
                nodeArr[i].radiusMult = 1;
            }
            nodeArr[i].bordersLimit = issetDefault(nodeArr[i].bordersLimit, false);
        }
    }
    
    function alertBox(text){
        
    }
}

