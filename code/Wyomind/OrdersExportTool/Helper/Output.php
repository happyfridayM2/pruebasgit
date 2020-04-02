<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Helper;

class Output extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * convert json to html
     * @param $model
     * @param string $opattern
     * @param $incrementalColumn
     * @param $incrementalColumnName
     * @param bool $header
     * @return string|null
     */
    public function jsonToTable($model, $opattern, $incrementalColumn, $incrementalColumnName, $header = false)
    {
        $pattern = preg_replace('/(\r|\n)/s', '', $opattern);
        $pattern = "[" . $pattern . "]";
        $lines = json_decode($pattern);
        $headline = null;
        $baseline = null;
        $tr = null;
        if (count($lines)) {
            foreach ($lines as $line) {
                $baseline = null;
                $headline = null;
                if (isset($line->header)) {
                    $data = $line->header;
                } else {
                    $data = $line->body;
                }

                if ($header) {
                    $tr .= "<thead><tr class='header'>";
                } else {
                    $tr .= "<tr>";
                }

                // ADD COLUMN INCREMENT OPTION
                if ($incrementalColumn) {
                    if ($header) {
                        $tr .= "<th>" . $incrementalColumnName . "</th>";
                    } else {
                        $tr .= "<td>" . $model->inc . "</td>";
                        $model->inc++;
                    }
                }

                foreach (array_values($data) as $value) {
                    $v = ($value != null) ? $value : "<span>(empty)</span>";

                    if (strstr($value, "/breakline/") !== false) {
                        $tr .= "</tr><tr>";
                        $v = str_replace("/breakline/", "", $v);
                    }

                    if (strstr($value, "/headline/") !== false) {
                        if (!isset($line->header)) {
                            $headline .= "<td>" . str_replace("/headline/", "", $v) . "</td>";
                        } else {
                            $headline .= null;
                        }
                    } elseif (strstr($value, "/baseline/") !== false) {
                        if (!isset($line->header)) {
                            $baseline .= "<td>" . str_replace("/baseline/", "", $v) . "</td>";
                        } else {
                            $baseline .= null;
                        }
                    } else {
                        if ($header) {
                            $tr .= "<th>" . $v . "</th>";
                        } else {
                            $tr .= "<td>" . $v . "</td>";
                        }
                    }
                }

                if ($header) {
                    $tr .= "</tr></thead>";
                } else {
                    $tr .= "</tr>";
                }
            }
        }

        if ($headline != null) {
            $tr = "<tr>" . $headline . "</tr>" . $tr;
        } elseif ($baseline != null) {
            $tr .= "<tr>" . $baseline . "</tr>";
        }

        return $tr;
    }

    /**
     * Convert json to a csv sting
     * @param $model
     * @param string $ooutput
     * @param string $separator
     * @param string $protector
     * @param string $escaper
     * @param $incrementalColumn
     * @param $incrementalColumnName
     * @param bool $breakline
     * @return string|null
     */
    public function jsonToStr($model, $ooutput, $separator, $protector, $escaper, $incrementalColumn, $incrementalColumnName, $breakline = true)
    {
        $output = preg_replace('/(\r\n|\n|\r|\r\n)/s', '', $ooutput);
        $output = "[" . $output . "]";
        $lines = json_decode($output);
        $headline = null;
        $baseline = null;
        $line = null;
        $i = 0;
        if (count($lines)) {
            foreach ($lines as $data) {
                $linetest = false;
                $baseline = null;
                $headline = null;
                $br = 0;
                if ($breakline || $i > 0) {
                    $line .= "\r\n";
                }

                if ($separator == '\t') {
                    $separator = "\t";
                }

                if (isset($data->header)) {
                    $data = $data->header;
                    $isHeader = true;
                    // ADD COLUMN INCREMENT OPTION
                    if ($incrementalColumn) {
                        $line .= $protector . $this->escapeStr($incrementalColumnName, $protector, $escaper) . $protector . $separator;
                    }
                } elseif (!json_decode($output)) {
                    return "";
                }

                if (isset($data->body)) {
                    $data = $data->body;
                    if ($incrementalColumn) {
                        $line .= $model->inc . $separator;
                        $model->inc++;
                    }
                }

                $isHeader = false;
                // ADD COLUMN INCREMENT OPTION

                $u = 0;
                foreach (array_values($data) as $value) {
                    if ($br > 0) {
                        $br = 2;
                    }

                    if (strstr($value, "/breakline/")) {
                        $br = 1;
                    }

                    if ($u > 0 && $br < 2) {
                        if (strstr($value, "/headline/")) {
                            if (!$isHeader && $headline != null) {
                                $headline .= $separator;
                            } else {
                                $headline .= null;
                            }
                        } elseif (strstr($value, "/baseline/")) {
                            if (!$isHeader && $baseline != null) {
                                $baseline .= $separator;
                            } else {
                                $baseline .= null;
                            }
                        } elseif (($linetest)) {
                            $line .= $separator;
                        }
                    }

                    if ($br > 1) {
                        $br = 0;
                    }

                    if ($protector != "") {
                        if (strstr($value, "/headline/")) {
                            if (!$isHeader) {
                                $headline .= $protector . $this->escapeStr(str_replace("/headline/", "", $value), $protector, $escaper) . $protector;
                            } else {
                                $headline .= null;
                            }
                        } elseif (strstr($value, "/baseline/")) {
                            if (!$isHeader) {
                                $baseline .= $protector . $this->escapeStr(str_replace("/baseline/", "", $value), $protector, $escaper) . $protector;
                            } else {
                                $baseline .= null;
                            }
                        } else {
                            $linetest = true;
                            $line .= $protector . $this->escapeStr(str_replace("/breakline/", "", $value), $protector, $escaper) . $protector;
                        }

                        if (strstr($value, "/breakline/")) {
                            $line .= "\r\n";
                        }
                    } else {
                        $value = str_replace("/breakline/", "\r\n", $value);
                        if (strstr($value, "/headline/")) {
                            if (!$isHeader) {
                                $headline .= $this->escapeStr(str_replace("/headline/", "", $value), $separator, $escaper);
                            } else {
                                $headline .= null;
                            }
                        } elseif (strstr($value, "/baseline/")) {
                            if (!$isHeader) {
                                $baseline .= $this->escapeStr(str_replace("/baseline/", "", $value), $separator, $escaper);
                            } else {
                                $baseline .= null;
                            }
                        } else {
                            $line .= $this->escapeStr($value, $separator, $escaper);
                            $linetest = true;
                        }
                    }
                    $u++;
                }

                if ($separator == "[|]") {
                    $line .= "[:]";
                }

                $i++;
            }
        }

        if ($headline != null) {
            $line = $headline . "\r\n" . $line . "\r\n";
            $line = str_replace("\r\n\r\n", "\r\n", $line);
        } elseif ($baseline != null) {
            $line .= "\r\n" . $baseline;
        }

        return $line;
    }

    /**
     * Escape a string with a given character
     * @param string $pattern
     * @param string $escapedChar
     * @param string $escaper
     * @return string
     */
    public function escapeStr($pattern, $escapedChar = '"', $escaper = "\\")
    {
        return str_replace($escapedChar, $escaper . $escapedChar, $pattern);
    }

    /**
     * Render a xml string with CDATA
     * @param string $output
     * @param boolean $enclose
     * @param int $type
     * @return string
     */
    public function xmlEncloseData($output, $enclose = true, $type = 1)
    {

        $pattern = '/(<[^>^\/]+>?)([^<\s]*)(<\/[^>]+>)/s';
        $matches = [];
        preg_match_all($pattern, $output, $matches);
        foreach (array_keys($matches[1]) as $key) {
            $tagContent = trim($matches[2][$key]);
            if (!empty($tagContent) || is_numeric($tagContent)) {
                if ($enclose && $type == 1) {
                    $output = str_replace($matches[0][$key], ($matches[1][$key]) . '<![CDATA[' . $tagContent . ']]>' . ($matches[3][$key]), $output);
                } else {
                    $output = str_replace($matches[0][$key], ($matches[1][$key]) . $tagContent . ($matches[3][$key]), $output);
                }
            }
        }

        $a = preg_split("/\n/s", $output);
        $o = '';
        foreach ($a as $line) {
            if (strlen(trim($line)) > 0) {
                $o .= $line . "\n";
            }
        }
        return $o;


    }

    /**
     * Encode string in q given charset
     * @param string $ovar
     * @param string $encoding
     * @return string
     */
    public function encode($ovar, $encoding)
    {
        $encoding = str_replace("-without-bom", null, $encoding);
        $var = htmlentities($ovar, ENT_NOQUOTES, 'UTF-8');
        $var = html_entity_decode($var, ENT_NOQUOTES, $encoding);
        return $var;
    }
}