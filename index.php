<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Graphique avec d3js</title>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

</head>
<!--https://gist.github.com/jsrubin/1fe23a1c6e287dcdc104c46f377b0235-->
<style>
    #svg {
        display: block;
        margin: auto;
    }

    #chart {
        margin-top: 20px;
        /*border: 1px solid #DEDEDE;*/
    }

    .bar {
        fill: steelblue;
    }

    .bar:hover {
        fill: #e7bd15;
    }

    .chart-tooltip {
        position: absolute;
        opacity:0.8;
        z-index:1000;
        text-align:left;
        border-radius:4px;
        -moz-border-radius:4px;
        -webkit-border-radius:4px;
        padding:8px;
        color:#fff;
        background-color:#000;
        font: 12px sans-serif;
        max-width: 300px;
    }
</style>
<body>

<div class="container">
    <div class="row">

        <div class="col-xs-12 col-lg-6">
            <div id="svg1"></div>
        </div>
        <div class="col-xs-12 col-lg-6">
            <div id="chart"></div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<script>
    const data = [
        {"position": 1, "country": "Chine", "population": 1355045511},
        {"position": 2, "country": "Inde", "population": 1210193422},
        {"position": 3, "country": "États-Unis", "population": 315664478},
        {"position": 4, "country": "Indonésie", "population": 237641326},
        {"position": 5, "country": "Brésil", "population": 193946886},
        {"position": 6, "country": "Pakistan", "population": 182614855},
        {"position": 7, "country": "Nigeria", "population": 174507539},
        {"position": 8, "country": "Bangladesh", "population": 152518015},
        {"position": 9, "country": "Russie", "population": 143056383},
        {"position": 10, "country": "Japon", "population": 127650000}
    ];

    svg = d3.select("#svg1").append("svg");

    var group = svg.append("g");

    group.selectAll(".node")
        .data(data)
        .enter()
        .append("rect")
        .attr("x", d => d.position * 30)
        .attr("y", 0)
        .attr("width", 20)
        .attr("height", d => d.population / 10000000);


    //teste histogramme
    const margin = {top: 20, right: 20, bottom: 90, left: 120},
        width = 800 - margin.left - margin.right,
        height = 400 - margin.top - margin.bottom;


    const x = d3.scaleBand()
        .range([0, width])
        .padding(0.1);

    const y = d3.scaleLinear()
        .range([height, 0]);
    const svg2 = d3.select("#chart").append("svg")
        .attr("id", "svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    const div = d3.select("body").append("div")
        .attr("class", "chart-tooltip")
        .style("opacity", 0);

    // Conversion des caractères en nombres
    data.forEach(d => d.population = +d.population);

    // Mise en relation du scale avec les données de notre fichier
    // Pour l'axe X, c'est la liste des pays
    // Pour l'axe Y, c'est le max des populations
    x.domain(data.map(d => d.country));
    y.domain([0, d3.max(data, d => d.population)]);

    // Ajout de l'axe X au SVG
    // Déplacement de l'axe horizontal et du futur texte (via la fonction translate) au bas du SVG
    // Selection des noeuds text, positionnement puis rotation
    svg2.append("g")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom(x).tickSize(0))
        .selectAll("text")
        .style("text-anchor", "end")
        .attr("dx", "-.8em")
        .attr("dy", ".15em")
        .attr("transform", "rotate(-65)");

    // Ajout de l'axe Y au SVG avec 6 éléments de légende en utilisant la fonction ticks (sinon D3JS en place autant qu'il peut).
    svg2.append("g")
        .call(d3.axisLeft(y).ticks(6));

    // Ajout des bars en utilisant les données de notre fichier data.tsv
    // La largeur de la barre est déterminée par la fonction x
    // La hauteur par la fonction y en tenant compte de la population
    // La gestion des events de la souris pour le popup
    svg2.selectAll(".bar")
        .data(data)
        .enter().append("rect")
        .attr("class", "bar")
        .attr("x", d => x(d.country))
        .attr("width", x.bandwidth())
        .attr("y", d => y(d.population))
        .attr("height", d => height - y(d.population))
        .on("mouseover", function (event, d) {
            div.transition()
                .duration(200)
                .style("opacity", .9);
            div.html("Population : " + d.population)
                .style("left", (event.pageX + 10) + "px")
                .style("top", (event.pageY - 50) + "px");
        })
        .on("mouseout", function (event, d) {
            div.transition()
                .duration(500)
                .style("opacity", 0);
        });




    function movingAverage(array, count) {
        var result = [], val;

        for (var i = Math.floor(count / 2), len = array.length - count / 2; i < len; i++) {
            val = d3.mean(array.slice(i - count / 2, i + count / 2), d => d.Total);
            result.push({"date": array[i].date, "value": val});
        }

        return result;
    }
    const line = d3.line()
        .x(d => (x.bandwidth() / 2) + x(d.population)) // décalage pour centrer au milieu des barres
        .y(d => y(d.value))
        .curve(d3.curveMonotoneX); // Fonction de courbe permettant de l'adoucir

    let mm10array = movingAverage(data, 10); // Moyenne mobile sur 10 jours

    svg.append("path")
        .datum(mm10array)
        .attr("d", line)
        .style("fill", "none")
        .style("stroke", "#ffab00")
        .style("stroke-width", 3);

    let lastEntry = mm10array[mm10array.length - 1];
    svg.append("text")
        .attr("transform", "translate(" + x(lastEntry.population) + "," + y(lastEntry.value) + ")") // Le dernier point de la courbe
        .attr("dx", "0.5em") // Auquel on ajoute un léger décalage sur X
        .attr("dy", "0.5em") // et sur Y
        .style("fill", "#ffab00")
        .style("font-size", "0.8em")
        .style("font-weight", "500")
        .text("MM10");
</script>
</body>
</html>