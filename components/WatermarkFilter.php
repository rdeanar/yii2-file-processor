<?php

namespace deanar\fileProcessor\components;


/**
 * Usage example:
 *
 * $imagine = new Imagine();
 * $image = $imagine->open('testimage.jpg');
 *
 * $image = $image->thumbnail(new Box(320, 320), ImageInterface::THUMBNAIL_INSET);
 *
 * $filter = new WatermarkFilter($imagine, 'wm.png', WatermarkFilter::WM_POSITION_BOTTOM_RIGHT, 5);
 * $image = $filter->apply($image);
 *
 * $image->save('testimage2.jpg');
 *
 * Class WatermarkFilter
 * @package deanar\fileProcessor\components
 */

class WatermarkFilter implements \Imagine\Filter\FilterInterface
{
    const WM_POSITION_TOP_LEFT      = 'tl';
    const WM_POSITION_TOP_RIGHT     = 'tr';
    const WM_POSITION_BOTTOM_LEFT   = 'bl';
    const WM_POSITION_BOTTOM_RIGHT  = 'br';
    const WM_POSITION_CENTER        = 'center';

    private $imagine;
    private $wm_position;
    private $wm_margin;
    private $wm_path;

    private $pos_vertical = 0;
    private $pos_horizontal = 0;

    public function __construct(\Imagine\Image\ImagineInterface $imagine, $wm_path, $wm_position=self::WM_POSITION_BOTTOM_RIGHT, $wm_margin=5)
    {
        $this->imagine      = $imagine;

        $this->wm_path      = $wm_path;
        $this->wm_position  = $wm_position;
        $this->wm_margin    = $wm_margin >= 0 ? $wm_margin : 0;
    }

    /**
     * @inheritdoc
     * @param \Imagine\Image\ImageInterface $image
     * @return \Imagine\Image\ImageInterface|\Imagine\Image\ManipulatorInterface
     */
    public function apply(\Imagine\Image\ImageInterface $image)
    {
        if(!file_exists($this->wm_path)) return $image;
        \Yii::warning('Watermark does not exists: '. $this->wm_path);

        $watermark = $this->imagine->open($this->wm_path);

        $size       = $image->getSize();
        $wm_size    = $watermark->getSize();

        // Horizontal position
        switch ($this->wm_position) {
            case self::WM_POSITION_TOP_LEFT:
            case self::WM_POSITION_BOTTOM_LEFT:
                $this->pos_horizontal = $this->wm_margin;
                break;
            case self::WM_POSITION_TOP_RIGHT:
            case self::WM_POSITION_BOTTOM_RIGHT:
                $this->pos_horizontal = $size->getWidth() - $wm_size->getWidth() - $this->wm_margin;
                break;
            case self::WM_POSITION_CENTER:
                $this->pos_horizontal = ($size->getWidth() - $wm_size->getWidth())/2;
                break;
        }

        // Vertical position
        switch ($this->wm_position) {
            case self::WM_POSITION_TOP_LEFT:
            case self::WM_POSITION_TOP_RIGHT:
                $this->pos_vertical = $this->wm_margin;
                break;
            case self::WM_POSITION_BOTTOM_LEFT:
            case self::WM_POSITION_BOTTOM_RIGHT:
                $this->pos_vertical = $size->getHeight() - $wm_size->getHeight() - $this->wm_margin;
                break;
            case self::WM_POSITION_CENTER:
                $this->pos_vertical = ($size->getHeight() - $wm_size->getHeight())/2;
                break;
        }

        if($this->pos_horizontal <= 0)  $this->pos_horizontal   = 0;
        if($this->pos_vertical   <= 0)  $this->pos_vertical     = 0;

        $wm_position_point = new \Imagine\Image\Point($this->pos_horizontal, $this->pos_vertical);

        try {
            $image = $image->paste($watermark, $wm_position_point);
        } catch (\Imagine\Exception\OutOfBoundsException $e) {
            \Yii::warning($e->getMessage());
        }

        return $image;
    }
}