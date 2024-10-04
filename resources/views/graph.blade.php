<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dependency Graph</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.21.1/cytoscape.min.js"></script>
    <style>
        #cy {
            width: 800px;
            height: 600px;
            border: 1px solid #ccc;
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
                            var instability = ele.data('instability');
                            if (instability > 0.80) {
                                return 'red';
                            } else if (instability > 0.60) {
                                return 'orange';
                            } else {
                                return 'green';
                            }
                        },
                        'label': 'data(id)',
                        'color': 'black',
                        'text-valign': 'center'
                    }
                },
                {
                    selector: 'edge',
                    style: {
                        'width': 7,
                        'line-color': '#ccc',
                        'target-arrow-color': '#ccc',
                        'target-arrow-shape': 'triangle',
                        'curve-style': 'bezier'
                    }
                }
            ],
            layout: {
                name: 'breadthfirst',
                directed: true
            }
        });

        cy.nodes().forEach(function(ele) {
            ele.qtip({
                content: ele.data('id'),
                position: {
                    my: 'top center',
                    at: 'bottom center'
                },
                style: {
                    classes: 'qtip-bootstrap',
                    tip: {
                        width: 16,
                        height: 8
                    }
                }
            });
        });
    </script>
</body>

</html>