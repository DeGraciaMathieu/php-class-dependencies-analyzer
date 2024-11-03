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
    </style>
</head>
<body>
    <div id="cy"></div>
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
                                instability > 0.6 ? '#f0ad4e' :
                                instability > 0.4 ? '#5bc0de' : '#5cb85c';
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
    </script>
</body>

</html>