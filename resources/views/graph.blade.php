<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dependency Graph</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.21.1/cytoscape.min.js"></script>
    <style>
        #cy {
            width: 100%;
            height: 80vh;
            border: 1px solid #ccc;
            margin: auto;
        }

        #legend {
            display: flex;
            justify-content: center;
            margin-top: 10px;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin: 0 15px;
        }

        .color-box {
            width: 15px;
            height: 15px;
            margin-right: 5px;
            border-radius: 10px;
        }

        .color-high {
            background-color: #d9534f;
        }

        .color-medium {
            background-color: #f0ad4e;
        }

        .color-low {
            background-color: #5cb85c;
        }

        .color-edge {
            background-color: red;
            width: 30px;
            height: 3px;
        }
    </style>
</head>

<body>
    <div id="cy"></div>
    <div id="legend">
        <div class="legend-item">
            <div class="color-box color-high"></div> High Instability (> 0.8)
        </div>
        <div class="legend-item">
            <div class="color-box color-medium"></div> Medium Instability (> 0.4)
        </div>
        <div class="legend-item">
            <div class="color-box color-low"></div> Low Instability (≤ 0.4)
        </div>
        <div class="legend-item">
            <div class="color-edge"></div> Unstable Dependency (red arrow)
        </div>
    </div>
    <script>
        var data = {
            nodes: @json($nodes),
            edges: @json($edges)
        };

        var cy = cytoscape({
            container: document.getElementById('cy'),
            elements: {
                nodes: data.nodes,
                edges: data.edges
            },
            style: [{
                    selector: 'node',
                    style: {
                        'background-color': function(ele) {
                            let instability = ele.data('instability');
                            return instability > 0.8 ? '#d9534f' :
                                instability > 0.4 ? '#f0ad4e' : '#5cb85c';
                        },
                        'label': function(ele) {
                            const fullId = ele.data('id');
                            return fullId.split('\\').pop();
                        },
                        'color': '#333',
                        'font-size': '10px',
                        'text-outline-width': 2,
                        'text-outline-color': '#ffffff',
                        'text-valign': 'center',
                        'text-wrap': 'wrap',
                        'text-max-width': '80px'
                    }
                },
                {
                    selector: 'edge',
                    style: {
                        'width': 4,
                        'line-color': '#cccccc',
                        'target-arrow-color': '#cccccc',
                        'target-arrow-shape': 'triangle',
                        'curve-style': 'bezier',
                        'opacity': 0.8
                    }
                },
                {
                    selector: '.faded',
                    style: {
                        'opacity': 0.3,
                        'text-opacity': 0.1
                    }
                }
            ],
            layout: {
                name: 'cose',
                animate: true,
                animationDuration: 500
            }
        });

        cy.edges().forEach(function(edge) {

            var sourceInstability = edge.source().data('instability');
            var targetInstability = edge.target().data('instability');

            if (sourceInstability < targetInstability) {
                edge.style({
                    'line-color': 'red',
                    'target-arrow-color': 'red'
                });
            }
        });

        cy.on('select', 'node', function(evt) {

            var node = evt.target;

            cy.elements().addClass('faded');
            node.removeClass('faded');
            node.connectedEdges().removeClass('faded');
            node.connectedEdges().connectedNodes().removeClass('faded');
        });

        cy.on('unselect', 'node', function() {
            cy.elements().removeClass('faded');
        });
    </script>
</body>

</html>