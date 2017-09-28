<?php

class ImagickOptions {
    public $dest;
    public $src;
    public $dst_x;
    public $dst_y;
    public $src_x;
    public $src_y;
    public $src_w;
    public $src_h;

    /**
     * ImagickOptions constructor.
     *
     * @param $dest string name of image edited
     * @param $src string name of overlaying image
     * @param $dst_x int x coord on dest
     * @param $dst_y int y coord on dest
     * @param $src_x int x coord on src
     * @param $src_y int y coord on src
     * @param $src_w int width of src
     * @param $src_h int height of src
     */
    public function __construct($dest, $src, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h) {
        $this->dest = $dest;
        $this->src = $src;
        $this->dst_x = $dst_x;
        $this->dst_y = $dst_y;
        $this->src_x = $src_x;
        $this->src_y = $src_y;
        $this->src_w = $src_w;
        $this->src_h = $src_h;
    }

    static public function arrayConstruct($dest, $src, $options) {
        $instance = new self(
                $dest, $src,
                $options[0], $options[1], $options[2],
                $options[3], $options[4], $options[6]
        );

        return $instance;
    }


}