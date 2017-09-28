<?php
require_once 'vendor/autoload.php';

class DocumentWriter
{
    private $phpWord = null;
    private $body = null;

    public function __construct()
    {
        $this->phpWord = new \PhpOffice\PhpWord\PhpWord();
        $this->phpWord->setDefaultFontName('Calibri');
        $this->phpWord->setDefaultFontSize(12);
        $this->body = $this->phpWord->addSection(array('breakType' => 'continuous'));

        $this->header();
        $this->footer();
    }

    private function header()
    {
        $header = $this->body->addHeader();
        $header->addImage(
            'header.png',
            array(
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                'width' => 102,
                'height' => 67,
                'marginTop' => 10,
                'marginLeft' => 0,
                'borderStyle' => 'solid'
            )
        );
        $header->addText(
            "\n",
            array(
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            )
        );

    }

    private function footer()
    {
        $fontStyle = new \PhpOffice\PhpWord\Style\Font();
        $fontStyle->setBold(false);
        $fontStyle->setName('Calibri');
        $fontStyle->setSize(9);
        $footer = $this->body->addFooter();
        $footer->addText(
            "",
            $fontStyle,
            array(
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            )
        );
        $footer->addText(
            'Via Romagnosi 4, Varese, 21100',
            $fontStyle,
            array(
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            )
        );

        /**
         * Комментарии для добавления в нижний колонтитул
         * Если Вы хотите что то добавить в нижний колонтитул
         * то вам необходимо сделать следующее
         * скопировать текст который ниже
         *
         * $footer->addText(
         * 'СЮДА ВСТАВЬТЕ ВАШ ТЕКСТ',
         * $fontStyle,
         * array(
         * 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
         * )
         * );
         * и заменить текст в кавычках на ваш текст
         * Ниже вы можете видеть текущий колонтитул и текст в нем
         * вставьте вашу секцию с нужны
         **/

        $strings = ['+39.0332.1690363', 'info@mirafortis.com', 'mirafortis.com'];
        foreach ($strings as $string) {
            $footer->addText(
                $string,
                $fontStyle,
                array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER)
            );
        }
    }

    public function save()
    {
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($this->phpWord, 'Word2007');
        $objWriter->save('file.docx');
    }

    public function titleAndTable($title, $attributes, $price)
    {
        $fontTitle = new \PhpOffice\PhpWord\Style\Font();
        $fontTitle->setBold(true);
        $fontTitle->setName('Calibri');
        $fontTitle->setSize(18);
        $this->body->addText(
            $title . "\n", $fontTitle, array(
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER)
        );
        $fancyTableStyleName = 'Fancy Table';
        $fancyTableStyle = array('borderSize' => 0, 'borderColor' => '000000', 'cellMargin' => 0, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
        $fancyTableCellStyle = array('valign' => 'center');
        $fancyTableFontStyle = array('bold' => false);
        $this->phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle);
        $table = $this->body->addTable($fancyTableStyleName);

        foreach ($attributes as $key => $attribute) {
            if ($attribute != '') {
                $table->addRow();
                $table->addCell(4000, $fancyTableCellStyle)->addText($key, $fancyTableFontStyle);
                $table->addCell(4000, $fancyTableCellStyle)->addText($attribute, $fancyTableFontStyle);
            }
        }
    }

    public function map($maps)
    {
        $this->body->addPageBreak();
        $fontBold14 = new \PhpOffice\PhpWord\Style\Font();
        $fontBold14->setBold(true);
        $fontBold14->setName('Calibri');
        $fontBold14->setSize(14);
        $this->body->addText(
            "\n1. Расположение\n", $fontBold14, array(
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START)
        );
        foreach ($maps as $map) {
            $this->body->addImage($map, array(
                'width' => 600
            ));
        }
    }

    public function description($description)
    {
        $fontBold14 = new \PhpOffice\PhpWord\Style\Font();
        $fontBold14->setBold(true);
        $fontBold14->setName('Calibri');
        $fontBold14->setSize(14);

        $this->body->addText(
            "\n2. Состояние и характеристики\n", $fontBold14, array(
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START)
        );

        $font12 = new \PhpOffice\PhpWord\Style\Font();
        $font12->setBold(false);
        $font12->setName('Calibri');
        $font12->setSize(12);

        $this->body->addText(
            $description, $font12, array(
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH)
        );
        $this->body->addPageBreak();
    }

    public function images($images)
    {
        $fontBold14 = new \PhpOffice\PhpWord\Style\Font();
        $fontBold14->setBold(true);
        $fontBold14->setName('Calibri');
        $fontBold14->setSize(14);

        $this->body->addText(
            "\n3. Фото\n", $fontBold14, array(
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START)
        );
        foreach ($images as $image) {
            $this->body->addImage($image, array(
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
//                'height' => 250
                'width' => 600

            ));
            $this->body->addText(
                "\n", $fontBold14, array(
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER)
            );
        }
    }

    public function regards()
    {
        // create a font
        $font12 = new \PhpOffice\PhpWord\Style\Font();
        $font12->setBold(false);
        $font12->setName('Calibri');
        $font12->setSize(12);

        // set locale
        setlocale(LC_ALL, "rus");
        $date = strftime(" %d/%m/%Y", time());

        $strings = [
            "\nС уважением,\n" => 1,
            "\nКоманда Мирафортис\n" => 1,
            "\nВарезе, $date" => 2
        ];
        foreach ($strings as $key => $string) {
            $this->body->addText(
                $key, $font12, array(
                    'alignment' => $string == 1
                        ? \PhpOffice\PhpWord\SimpleType\Jc::START
                        : \PhpOffice\PhpWord\SimpleType\Jc::END
                )
            );
        }
    }
}