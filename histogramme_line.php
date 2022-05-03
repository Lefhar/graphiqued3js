<!DOCTYPE html>
<!--
Copyright 2017 Walmart
This work is licensed under Apache License, Version 2.0 (the “License”); (the "License"); you may
not use this file except in compliance with the License. You may obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
-->
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Histogram With Ideal Curve</title>
    <meta name="description" content="">
    <script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <style>
        body {
            font: 10px sans-serif;
        }

        .axis path,
        .line {
            fill: none;
            stroke: steelblue;
            stroke-width: 1.5px;
        }
    </style>
</head>

<body>
<div class="container">
    <div class="row">
        <div id="graphique"></div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
<script type="text/javascript">
    let margin = {
            top: 20,
            right: 20,
            bottom: 30,
            left: 50
        },
        width = 960 - margin.left - margin.right,
        height = 500 - margin.top - margin.bottom;
    let series = [
        'Actual', 'Ideal'
    ];
    // Calculate the color based on the number of series
    let color = d3.scale.category10();
    color.domain(series);
    // A formatter for counts.
    let formatCount = d3.format(',.0f');
    // calculations for plotting ideal/normal distribution curve
    let numBuckets = 20;
    let numberOfDataPoints = 1000;
    let mean = 20;
    let stdDeviation = 5;
    // Generate a 1000 data points using normal distribution with mean=20, deviation=5
    let normalDistributionFunction = d3.random.normal(mean, stdDeviation);
    let actualData = d3.range(numberOfDataPoints).map(normalDistributionFunction);
    let sum = d3.sum(actualData);
    let probability = 1 / numberOfDataPoints;
    let letiance = sum * probability * (1 - probability);
    let idealData = getProbabilityData(actualData, mean, letiance);
    let max = d3.max(actualData);
    let min = d3.min(actualData);
    // x axis scaler function
    let x = d3.scale.linear()
        .range([0, width])
        .domain([min, max]);
    // Generate a histogram using twenty uniformly-spaced bins.
    let dataBar = d3.layout.histogram()
        .bins(numBuckets)(actualData);
    let yMax = d3.max(dataBar, function (d) {
        return d.length;
    });
    let y = d3.scale.linear()
        .domain([0, yMax])
        .range([height, 0]);
    let xAxis = d3.svg.axis()
        .scale(x)
        .ticks(10)
        .orient('bottom');
    let yAxis = d3.svg.axis()
        .scale(y)
        .orient('left')
        .tickFormat(d3.format('.2s'));
    // normalized X Axis scaler function
    let xNormal = d3.scale.linear()
        .range([0, width])
        .domain(d3.extent(idealData, function (d) {
            return d.q;
        }));
    // normalized Y Axis scaler function
    let yNormal = d3.scale.linear()
        .range([height, 0])
        .domain(d3.extent(idealData, function (d) {
            return d.p;
        }));
    // line plot function
    let linePlot = d3.svg.line()
        .x(function (d) {
            return xNormal(d.q);
        })
        .y(function (d) {
            return yNormal(d.p);
        });
    // Attach to body
    let svg = d3.select("#graphique").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
    // draw histogram bars
    let bar = svg.selectAll('.bar')
        .data(dataBar)
        .enter().append('g')
        .attr('class', 'bar')
        .attr('transform', function (d) {
            return 'translate(' + x(d.x) + ',' + y(d.y) + ')';
        });
    bar.append('rect')
        .attr('x', 1)
        .attr('width', function (d) {
            return (x(d.dx) - x(0)) <= 0 ? 0 : (x(d.dx) - x(0)) - 1;
        })
        .attr('height', function (d) {
            return height - y(d.y);
        })
        .attr('fill', function () {
            return color(series[0]);
        });
    bar.append('text')
        .attr('dy', '.75em')
        .attr('y', -12)
        .attr('x', (x(dataBar[0].dx) - x(0)) / 2)
        .attr('text-anchor', 'middle')
        .text(function (d) {
            return formatCount(d.y);
        });
    // draw ideal normal distribution curve
    let lines = svg.selectAll('.series')
        .data([1]) // only plot a single line
        .enter().append('g')
        .attr('class', 'series');
    // Add the Ideal lines
    lines.append('path')
        .datum(idealData)
        .attr('class', 'line')
        .attr('d', linePlot)
        .style('stroke', function () {
            return color(series[1]);
        })
        .style({'stroke-width': '2px', 'fill': 'none'});
    // Add the X Axis
    svg.append('g')
        .attr('class', 'x axis')
        .attr('transform', 'translate(0,' + height + ')')
        .call(xAxis);
    // Add the Y Axis
    svg.append('g')
        .attr('class', 'y axis')
        .call(yAxis);

    function getProbabilityData(normalizedData, m, v) {
        let data = [];
        // probabily - quantile pairs
        for (let i = 0; i < normalizedData.length; i += 1) {
            let q = normalizedData[i],
                p = probabilityDensityCalculation(q, m, v),
                el = {
                    'q': q,
                    'p': p
                };
            data.push(el);
        }
        ;
        data.sort(function (x, y) {
            return x.q - y.q;
        });
        return data;
    };
    // The probability density of the normal distribution
    // https://en.wikipedia.org/wiki/Normal_distribution
    function probabilityDensityCalculation(x, mean, letiance) {
        let m = Math.sqrt(2 * Math.PI * letiance);
        let e = Math.exp(-Math.pow(x - mean, 2) / (2 * letiance));
        return e / m;
    };
</script>
</body>
</html>