<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +-----------------------------------------------------------------------------+
// | Copyright (c) 2003 Sérgio Gonçalves Carvalho                                |
// +-----------------------------------------------------------------------------+
// | This file is part of Structures_Graph.                                      |
// |                                                                             |
// | Structures_Graph is free software; you can redistribute it and/or modify    |
// | it under the terms of the GNU Lesser General Public License as published by |
// | the Free Software Foundation; either version 2.1 of the License, or         |
// | (at your option) any later version.                                         |
// |                                                                             |
// | Structures_Graph is distributed in the hope that it will be useful,         |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
// | GNU Lesser General Public License for more details.                         |
// |                                                                             |
// | You should have received a copy of the GNU Lesser General Public License    |
// | along with Structures_Graph; if not, write to the Free Software             |
// | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA                    |
// | 02111-1307 USA                                                              |
// +-----------------------------------------------------------------------------+
// | Author: Sérgio Carvalho <sergio.carvalho@portugalmail.com>                  |
// +-----------------------------------------------------------------------------+
//
/**
 * The Graph.php file contains the definition of the Structures_Graph class 
 *
 * @package Structures_Graph
 */

/* dependencies {{{ */
require_once 'PEAR.php';
require_once 'Structures/Graph/Node.php';
/* }}} */

define('STRUCTURES_GRAPH_ERROR_GENERIC', 100);

/* class Structures_Graph {{{ */
/**
 * The Structures_Graph class represents a graph data structure. 
 *
 * A Graph is a data structure composed by a set of nodes, connected by arcs.
 * Graphs may either be directed or undirected. In a directed graph, arcs are 
 * directional, and can be traveled only one way. In an undirected graph, arcs
 * are bidirectional, and can be traveled both ways.
 *
 * @author    Sérgio Carvalho <sergio.carvalho@portugalmail.com> 
 * @copyright (c) 2004 by Sérgio Carvalho
 * @package   Structures_Graph
 */
/* }}} */
class Structures_Graph
{
    /**
     * List of node objects in this graph
     * @access private
     */
    var $_nodes = array();

    /**
     * If the graph is directed or not
     * @access private
     */
    var $_directed = false;


    /**
     * Constructor
     *
     * @param boolean $directed Set to true if the graph is directed.
     *                          Set to false if it is not directed.
     */
    public function __construct($directed = true)
    {
        $this->_directed = $directed;
    }

    /**
     * Old constructor (PHP4-style; kept for BC with extending classes)
     *
     * @param boolean $directed Set to true if the graph is directed.
     *                          Set to false if it is not directed.
     *
     * @return void
     */
    public function Structures_Graph($directed = true)
    {
        $this->__construct($directed);
    }

    /**
     * Return true if a graph is directed
     *
     * @return boolean true if the graph is directed
     */
    public function isDirected()
    {
        return (boolean) $this->_directed;
    }

    /**
     * Add a Node to the Graph
     *
     * @param Structures_Graph_Node $newNode The node to be added.
     *
     * @return void
     */
    public function addNode(&$newNode)
    {
        // We only add nodes
        if (!is_a($newNode, 'Structures_Graph_Node')) {
            return Pear::raiseError(
                'Structures_Graph::addNode received an object that is not'
                . ' a Structures_Graph_Node',
                STRUCTURES_GRAPH_ERROR_GENERIC
            );
        }

        //Graphs are node *sets*, so duplicates are forbidden.
        // We allow nodes that are exactly equal, but disallow equal references.
        foreach ($this->_nodes as $key => $node) {
            /*
             ZE1 equality operators choke on the recursive cycle introduced
             by the _graph field in the Node object.
             So, we'll check references the hard way
             (change $this->_nodes[$key] and check if the change reflects in
             $node)
            */
            $savedData = $this->_nodes[$key];
            $referenceIsEqualFlag = false;
            $this->_nodes[$key] = true;
            if ($node === true) {
                $this->_nodes[$key] = false;
                if ($node === false) {
                    $referenceIsEqualFlag = true;
                }
            }
            $this->_nodes[$key] = $savedData;
            if ($referenceIsEqualFlag) {
                return Pear::raiseError(
                    'Structures_Graph::addNode received an object that is'
                    . ' a duplicate for this dataset',
                    STRUCTURES_GRAPH_ERROR_GENERIC
                );
            }
        }
        $this->_nodes[] =& $newNode;
        $newNode->setGraph($this);
    }

    /**
     * Remove a Node from the Graph
     *
     * @param Structures_Graph_Node $node The node to be removed from the graph
     *
     * @return void
     * @todo   This is unimplemented
     */
    public function removeNode(&$node)
    {
    }

    /**
     * Return the node set, in no particular order.
     * For ordered node sets, use a Graph Manipulator insted.
     *
     * @return array The set of nodes in this graph
     * @see    Structures_Graph_Manipulator_TopologicalSorter
     */
    public function &getNodes()
    {
        return $this->_nodes;
    }
}
?>
