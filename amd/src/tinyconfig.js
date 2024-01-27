// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @copyright  2024 Andreas Grabs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Export our init method.
 *
 * @returns {object} A simple configuration for tinymce.
 */
export const getTinyConfig = () => {
    return {
        "plugins": {
            "anchor": {
                "buttons": [
                    "anchor"
                ],
                "menuitems": {
                    "anchor": "insert"
                }
            },
            "charmap": {
                "buttons": [
                    "charmap"
                ],
                "menuitems": {
                    "charmap": "insert"
                }
            },
            "code": {
                "buttons": [
                    "code"
                ],
                "menuitems": {
                    "code": "view"
                }
            },
            "codesample": {
                "buttons": [
                    "codesample"
                ],
                "menutiems": {
                    "codesample": "insert"
                }
            },
            "directionality": {
                "buttons": [
                    "ltr",
                    "rtl"
                ]
            },
            "emoticons": {
                "buttons": [
                    "emoticons"
                ],
                "menuitems": {
                    "emoticons": "insert"
                }
            },
            "fullscreen": {
                "buttons": [
                    "fullscreen"
                ],
                "menuitems": {
                    "fullscreen": "view"
                }
            },
            "help": {
                "buttons": [
                    "help"
                ],
                "menuitems": {
                    "help": "help"
                }
            },
            "insertdatetime": {
                "buttons": [
                    "insertdatetime"
                ],
                "menuitems": {
                    "insertdatetime": "insert"
                }
            },
            "lists": {
                "buttons": [
                    "bullist",
                    "numlist"
                ]
            },
            "nonbreaking": {
                "buttons": [
                    "nonbreaking"
                ],
                "menuitems": {
                    "nonbreaking": "insert"
                }
            },
            "pagebreak": {
                "buttons": [
                    "pagebreak"
                ],
                "menuitems": {
                    "pagebreak": "insert"
                }
            },
            "quickbars": {
                "buttons": [
                    "quickimage",
                    "quicklink",
                    "quicktable"
                ]
            },
            "save": {
                "buttons": [
                    "cancel",
                    "save"
                ]
            },
            "searchreplace": {
                "buttons": [
                    "searchreplace"
                ],
                "menuitems": {
                    "searchreplace": "edit"
                }
            },
            "table": {
                "buttons": [
                    "table",
                    "tablecellprops",
                    "tablecopyrow",
                    "tablecutrow",
                    "tabledelete",
                    "tabledeletecol",
                    "tabledeleterow",
                    "tableinsertdialog",
                    "tableinsertcolafter",
                    "tableinsertcolbefore",
                    "tableinsertrowafter",
                    "tableinsertrowbefore",
                    "tablemergecells",
                    "tablepasterowafter",
                    "tablepasterowbefore",
                    "tableprops",
                    "tablerowprops",
                    "tablesplitcells",
                    "tableclass",
                    "tablecellclass",
                    "tablecellvalign",
                    "tablecellborderwidth",
                    "tablecellborderstyle",
                    "tablecaption",
                    "tablecellbackgroundcolor",
                    "tablecellbordercolor",
                    "tablerowheader",
                    "tablecolheader"
                ],
                "menuitems": {
                    "inserttable": "table",
                    "tableprops": "table",
                    "deletetable": "table",
                    "cell": "table",
                    "tablemergecells": "table",
                    "tablesplitcells": "table",
                    "tablecellprops": "table",
                    "column": "table",
                    "tableinsertcolumnbefore": "table",
                    "tableinsertcolumnafter": "table",
                    "tablecutcolumn": "table",
                    "tablecopycolumn": "table",
                    "tablepastecolumnbefore": "table",
                    "tablepastecolumnafter": "table",
                    "tabledeletecolumn": "table",
                    "row": "table",
                    "tableinsertrowbefore": "table",
                    "tableinsertrowafter": "table",
                    "tablecutrow": "table",
                    "tablecopyrow": "table",
                    "tablepasterowbefore": "table",
                    "tablepasterowafter": "table",
                    "tablerowprops": "table",
                    "tabledeleterow": "table"
                }
            },
            "visualblocks": {
                "buttons": [
                    "visualblocks"
                ],
                "menuitems": {
                    "visualblocks": "view"
                }
            },
            "visualchars": {
                "buttons": [
                    "visualchars"
                ],
                "menuitems": {
                    "visualchars": "view"
                }
            },
            "wordcount": {
                "buttons": [
                    "wordcount"
                ],
                "menuitems": {
                    "wordcount": "tools"
                }
            },
            "tiny_accessibilitychecker/plugin": {
                "buttons": [
                    "tiny_accessibilitychecker/tiny_accessibilitychecker_image"
                ],
                "menuitems": [
                    "tiny_accessibilitychecker/tiny_accessibilitychecker_image"
                ],
                "config": {
                    "permissions": {
                        "upload": true
                    },
                    "storeinrepo": true
                }
            },
            "tiny_autosave/plugin": {
                "config": {
                    "autosave": null
                }
            },
            "tiny_equation/plugin": {
                "buttons": [
                    "tiny_equation/equation"
                ],
                "menuitems": [
                    "tiny_equation/equation"
                ],
                "config": {
                    "texfilter": true,
                    "contextid": 1,
                    "libraries": [
                        {
                            "key": "group1",
                            "groupname": "Operators",
                            "elements": [
                                "\\cdot",
                                "\\times",
                                "\\ast",
                                "\\div",
                                "\\diamond",
                                "\\pm",
                                "\\mp",
                                "\\oplus",
                                "\\ominus",
                                "\\otimes",
                                "\\oslash",
                                "\\odot",
                                "\\circ",
                                "\\bullet",
                                "\\asymp",
                                "\\equiv",
                                "\\subseteq",
                                "\\supseteq",
                                "\\leq",
                                "\\geq",
                                "\\preceq",
                                "\\succeq",
                                "\\sim",
                                "\\simeq",
                                "\\approx",
                                "\\subset",
                                "\\supset",
                                "\\ll",
                                "\\gg",
                                "\\prec",
                                "\\succ",
                                "\\infty",
                                "\\in",
                                "\\ni",
                                "\\forall",
                                "\\exists",
                                "\\neq"
                            ],
                            "active": true
                        },
                        {
                            "key": "group2",
                            "groupname": "Arrows",
                            "elements": [
                                "\\leftarrow",
                                "\\rightarrow",
                                "\\uparrow",
                                "\\downarrow",
                                "\\leftrightarrow",
                                "\\nearrow",
                                "\\searrow",
                                "\\swarrow",
                                "\\nwarrow",
                                "\\Leftarrow",
                                "\\Rightarrow",
                                "\\Uparrow",
                                "\\Downarrow",
                                "\\Leftrightarrow"
                            ]
                        },
                        {
                            "key": "group3",
                            "groupname": "Greek symbols",
                            "elements": [
                                "\\alpha",
                                "\\beta",
                                "\\gamma",
                                "\\delta",
                                "\\epsilon",
                                "\\zeta",
                                "\\eta",
                                "\\theta",
                                "\\iota",
                                "\\kappa",
                                "\\lambda",
                                "\\mu",
                                "\\nu",
                                "\\xi",
                                "\\pi",
                                "\\rho",
                                "\\sigma",
                                "\\tau",
                                "\\upsilon",
                                "\\phi",
                                "\\chi",
                                "\\psi",
                                "\\omega",
                                "\\Gamma",
                                "\\Delta",
                                "\\Theta",
                                "\\Lambda",
                                "\\Xi",
                                "\\Pi",
                                "\\Sigma",
                                "\\Upsilon",
                                "\\Phi",
                                "\\Psi",
                                "\\Omega"
                            ]
                        },
                        {
                            "key": "group4",
                            "groupname": "Advanced",
                            "elements": [
                                "\\sum{a,b}",
                                "\\sqrt[a]{b+c}",
                                "\\int_{a}^{b}{c}",
                                "\\iint_{a}^{b}{c}",
                                "\\iiint_{a}^{b}{c}",
                                "\\oint{a}",
                                "(a)",
                                "[a]",
                                "\\lbrace{a}\\rbrace",
                                "\\left| \\begin{matrix} a_1 & a_2 \\\\ a_3 & a_4 \\end{matrix} \\right|",
                                "\\frac{a}{b+c}",
                                "\\vec{a}",
                                "\\binom {a} {b}",
                                "{a \\brack b}",
                                "{a \\brace b}"
                            ]
                        }
                    ],
                    "texdocsurl": "https://docs.moodle.org/401/en/Using_TeX_Notation"
                }
            },
            "tiny_link/plugin": {
                "buttons": [
                    "tiny_link/tiny_link_link",
                    "tiny_link/tiny_link_unlink"
                ],
                "menuitems": [
                    "tiny_link/tiny_link_link"
                ],
                "config": {
                    "permissions": {
                        "filepicker": false
                    }
                }
            }
        },
        "nestedmenu": true
    };
};
