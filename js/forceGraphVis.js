function create_force_visualization(pageElemSelector, config){
    //create an object to return that will hold the layout, properties and the visualization
    var o = {
        width : getProperty(config, "width", 960),
        height : getProperty(config, "height", 500),
        gravity : getProperty(config, "gravity", 0.05),
        charge : getProperty(config, "charge", -100),
        radius : getProperty(config, "radius", 6),
        defaultLinkDistance : getProperty(config, "defaultLinkDistance", 30),
        minLinkDistance: getProperty(config, "minLinkDistance", 10),
        maxLinkDistance: getProperty(config, "maxLinkDistance", 60),
        bordersLimit : getProperty(config, "bordersLimit", true), 
        fill : d3.scale.category20()
    };
    console.log(o);
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
                
    return o;
}

            
function init_visualization(graphObj, nodes, links){
    var linkColorScale = d3.scale.linear().domain([0, 0.125, 0.25, 0.375, 0.5, 0.625, 0.75, 0.875, 1])
    .range(["#0066FF", "#00CC99", "#00CC00", "#99CC00", "#FF9900", "#FF6600", "#FF3300", "#FF0000", "#B20000"]);
    
    var o = graphObj;
    console.log(o);
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
        return '<div class="text">' + node.name + '</div>';
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
    .on('mouseover', onMouseOver)
    .on('mouseout', onMouseOut)
    //    .call(d3.behavior.drag()
    //        .on('dragstart', dragStart)
    //        .on('drag', dragMove)
    //        .on('dragend', dragEnd) 
    //    );
    .call(o.layout.drag);
             
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
    }

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
    
    function onMouseOver(node){
        d3.select(this).select('.node').transition().duration(750)
        .style('width', "40px")
        .style('height', "40px")
        .style('border-radius', "20px");
    }
    function onMouseOut(node){
        d3.select(this).select('.node').transition().duration(750)
        .style('width', node.radius * 2 + "px")
        .style('height', node.radius * 2 + "px")
        .style('border-radius', node.radius + "px");
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
}

