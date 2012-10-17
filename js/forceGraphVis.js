function create_force_visualization(pageElemSelector, config){
    //create an object to return that will hold the layout, properties and the visualization
    var o = {};
    processConfig(o, config);
    
    //set up the layout (the physics context)
    o.layout = d3.layout.force()
    .gravity(o.config.gravity)
    .charge(o.config.charge)
    .linkDistance(function (link){
        var sourceRadius = isset(link.source.radius) ? link.source.radius : o.config.defaultRadius;
        var targetRadius = isset(link.target.radius) ? link.target.radius : o.config.defaultRadius;
        var lengthMult = issetDefault(link.lengthMult, 1);
        if(isset(link.value)){
            //we are assuming that link.value have normalized values between 0 and 1.
            var value = link.value < 0 ? 0 : link.value > 1 ? 1 : link.value;
            return (sourceRadius + targetRadius + o.config.linkMinimumDistance + o.config.linkMaximumDistance - 
                (o.config.linkMaximumDistance - o.config.linkMinimumDistance) * value) * lengthMult;
        }else{
            return (sourceRadius + targetRadius + o.config.linkDefaultDistance)*lengthMult;
        }

    })
    .size([o.config.width,o.config.height]);
    
    //element cleanup
    $(pageElemSelector).html("");
          
    //set up the visualization
    o.canvas = d3.select(pageElemSelector).style("width", o.config.width + "px").style("height", o.config.height + "px");
                
    //NOTE: similar to the 3D ambients, we have two contextes: the physics context and the visualization context
    //The physics context is were the elements directions are interpolated and position calculated.
    //The visualization context is what we going to show to the user (in the HTML!)
    //The physics context updates the visualization context, but both need to be set!
    //
    //visualization context = canvas (o.canvas)
    //physics context = layout (o.layout)
    
    
    o.isInitialized = false;
    o.isRunning = false;
    //maping some functions
    o.start = function(){
        o.layout.start();
        o.isRunning = true;
        return o;
    }
    o.stop = function(){
        o.layout.stop();
        o.isRunning = false;
        return o;
    }
    
    o.tick = function(){
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
        var transformStr = "translate(" +  (position[0] + ((o.config.width - (o.config.width * o.config.initialZoom))/2)) 
        + ',' + (position[1] + ((o.config.height - (o.config.height * o.config.initialZoom))/2)) + ")scale(" + value*o.config.initialZoom + ")";
        o.canvas.style("-webkit-transform", transformStr)
        .style("-moz-transform", transformStr)
        .style("-ms-transform", transformStr)
        .style("-o-transform", transformStr)
        .style("transform", transformStr);
        return o;
    }
    o.onTick = function() {
        function transform(d) {
            return "rotate(" + Math.atan2(
                (d.target.y + d.target.radius) - (d.source.y+ d.source.radius), 
                (d.target.x + d.target.radius) - (d.source.x + d.source.radius)
                ) * 180 / Math.PI + "deg)";
        }
        //calculates the link lenght. For l
        function length(d) 
        {
            var dx = (d.target.x + d.target.radius) - (d.source.x + d.source.radius),
            dy = (d.target.y + d.target.radius) - (d.source.y + d.source.radius);
            return Math.sqrt(dx * dx + dy * dy) + "px";
        }
        
        o.visNodes.style("left", function(d) {
            var pos = 0.0;
            if(o.config.bordersLimit || d.bordersLimit)
                pos = (d.x = Math.max(0, Math.min(o.config.width - d.radius * 2, d.x)));  //x bondaries limit
            else
                pos = d.x;
            return  pos + "px";
        })
        .style("top", function(d) {
            var pos = 0.0;
            if(o.config.bordersLimit || d.bordersLimit) 
                pos = (d.y = Math.max(0, Math.min(o.config.height - d.radius * 2, d.y))); //y bondaries limit
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
    
    o.onNodeMouseOver = function(node, index){
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
    o.onNodeMouseOut = function(node, index){
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
    o.onNodeClick = function(node, index){
        console.log("MouseClick event over node[" + index + "] \"" +  node.name + "\"");
        var selector = d3.select(this).select('.text');
        selector.transition().duration(500).text('CLICK!').each('end', function(){
            selector.text(node.name);
        });
    }
    o.onZoomAction = function(){
        //Zoom work in progress  
        var translatePos = d3.event.translate;
        var value = o.config.zoom;
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
    }
    
    o.onDragStart = function(d, i){
    //o.layout.stop()
    }
    o.onDragMove = function(d, i) {
    /*
        d.px += d3.event.dx;
        d.py += d3.event.dy;
        d.x += d3.event.dx;
        d.y += d3.event.dy; 
        tick(); // this is the key to make it work together with updating both px,py,x,y on d !
        */
    }
    o.onDragEnd = function(d, i){
    //o.layout.start()
    }
    //the default function
    o.defaultCleanupNodes = function (nodeArr){
        var size = nodeArr.length;
        for(var i = 0; i < size; i++)
        {
            if(issetDefault(nodeArr[i].nodeGroup, -1) == 0){
                nodeArr[i].radius = o.config.defaultRadius * 1.5;
            }else
            if(!isset(nodeArr[i].radius)){ //default radius check
                nodeArr[i].radius = o.config.defaultRadius;
            }
            if(isset(nodeArr[i].radiusMult)){
                nodeArr[i].radius *= nodeArr[i].radiusMult;
            }else{
                nodeArr[i].radiusMult = 1;
            }
            nodeArr[i].bordersLimit = issetDefault(nodeArr[i].bordersLimit, false);
        }
        o.onCleanupNodes(nodeArr);
    }
    o.placeNodes = function(nodesArr){
//        var n = nodesArr.length;
        var x = o.config.width/2, y = o.config.height /2;
//        var xx = o.config.width /10, yy = o.config.height / 10; 
        nodesArr.forEach(function(d, i) {
//            d.x = (x - yy / (2 * 3.14159) * i ) * Math.cos(i); 
//            d.y = (y - yy / (2 * 3.14159) * i ) * Math.cos(i); 
            d.x = x + Math.round(Math.random() * 30) * Math.pow(-1, i); 
            d.y = y + Math.round(Math.random() * 30) * Math.pow(-1, i);     
        });
    }
    o.onCleanupNodes = function(){}; //the substitute function
    /**
     * Toogle borders limit ON or OFF for this graph. Nodes cannot leave canvas when borders limit is ON.
     * Any node outside of the canvas will be thrown inside.
     */
    o.toggleBordersLimit = function(){
        o.stop();
        o.config.bordersLimit = !o.config.bordersLimit;
        o.tick();
        o.start();
        return o;
    }
    return o;
    
    function processConfig(o, config){
        o.config = {
            width : getProperty(config, "width", 960),
            height : getProperty(config, "height", 500),
            gravity : getProperty(config, "gravity", 0.05),
            charge : getProperty(config, "charge", -100),
            defaultRadius : getProperty(config, "defaultRadius", 6),
            linkDefaultDistance : getProperty(config, "linkDefaultDistance", 30),
            linkMinimumDistance : getProperty(config, "linkMinimumDistance", 10),
            linkMaximumDistance : getProperty(config, "linkMaximumDistance", 60),
            bordersLimit : getProperty(config, "bordersLimit", true),
            nodeFillScale : getProperty(config, "nodeFillScale", d3.scale.category20()),
            pzoom  : getProperty(config, "zoom", 1), //yes zoom is correct here!
            zoom : getProperty(config, "zoom", 1),
            minZoom : getProperty(config, "minZoom", 0.0625), //-4x
            maxZoom : getProperty(config, "maxZoom", 5), //5x
            initialZoom : getProperty(config, "initialZoom", o.zoom),
            linkColorScaleDomain : getProperty(config, "linkColorScaleDomain", null),
            linkColorScaleRange : getProperty(config, "linkColorScaleRange", null),
            linkColorScale : null
        };
        if(o.config.linkColorScale == null){
            //Custom scale is not defined. Lets use a default linear scale.
            o.config.linkColorScale = d3.scale.linear();
            //Are custom scale domains and ranges defined in config? If not, lets use default values.
            if(o.config.linkColorScaleDomain != null){
                o.config.linkColorScale.domain(o.config.linkColorScaleDomain); //config domain
            }else{
                o.config.linkColorScale.domain([0, 0.125, 0.25, 0.375, 0.5, 0.625, 0.75, 0.875, 1]); //default domain
            }
            if(o.config.linkColorScaleRange != null){
                o.config.linkColorScale.range(o.config.linkColorScaleRange); //config range
            }else{
                //default range
                o.config.linkColorScale.range(["#0066FF", "#00CC99", "#00CC00", "#99CC00", "#FF9900", "#FF6600", "#FF3300", "#FF0000", "#B20000"]); 
            }
        }else{ 
            //Custom scale in config found. Add the scale and/or if also defined in the config. 
            //If not we use the domain and range of the custom scale.
            if(o.config.linkColorScaleDomain != null){
                o.config.linkColorScale.domain(o.config.linkColorScaleDomain);
            }
            if(o.config.linkColorScaleRange != null){
                o.config.linkColorScale.range(o.config.linkColorScaleRange);
            }
        }
    //        
    //        
    //        
    //        getProperty(config, "linkColorScale",  d3.scale.linear.domain(o.config.linkColorScaleDomain).range(o.config.linkColorScaleRange)
    }
}

            
function init_visualization(graphObj, nodes, links){
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
            return o.config.linkColorScale(value); 
        }else{
            return "#000000";
        }
        
    })
    
    //call both cleanup function (the default and the custom if available)
    o.defaultCleanupNodes(nodes);
    //place the nodes in the visualization, otherwise they will be randomly placed
    o.placeNodes(nodes); 
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
        return (isset(d.radius) ? d.radius*2 : o.config.defaultRadius*2) + "px"; 
    })
    .style("height", function(d) { 
        return (isset(d.radius) ? d.radius*2 : o.config.defaultRadius*2) + "px"; 
    })
    .style("border-radius", function(d) { 
        return (isset(d.radius) ? d.radius : o.config.defaultRadius) + "px"; 
    })
    .style("background", function(d) {
        var nodeGroup = issetDefault(d.nodeGroup, 1);
        return o.config.nodeFillScale(nodeGroup);
    })
    .style("border-color", function(d) {
        var nodeGroup = issetDefault(d.nodeGroup, 1);
        return d3.rgb(o.config.nodeFillScale(nodeGroup)).darker();
    })
    //    .style('z-index', function(d){
    //        return issetDefault(d.zindex, o.nodeZindex);
    //    })
    .on('click', o.onNodeClick)
    .on('mouseover', o.onNodeMouseOver)
    .on('mouseout', o.onNodeMouseOut)
    .call(d3.behavior.drag()
        .on('dragstart', o.onDragStart)
        .on('drag', o.onDragMove)
        .on('dragend', o.onDragEnd) 
        )
    .call(o.layout.drag)
    .call(d3.behavior.zoom().on("zoom", o.onZoomAction));

    //tell the physics how to update and to start
    o.layout
    .nodes(nodes)
    .links(links)
    .on("tick", o.onTick)
    .start();
    
    o.isInitialized = o.isRunning = true;
}

function update_visualization(graphObj, nodes, links){
    
}