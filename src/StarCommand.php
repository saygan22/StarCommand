<?php

namespace Star;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StarCommand extends Command
{
    protected static $defaultName = 'star';

    protected function configure()
    {

        // define description and help message
        $this
            ->setName('width')
            ->setDescription('Width star')
            ->setHelp('Parametr k označení výšky a šířky výsledného obrázku');

        $this
            ->setName('color')
            ->setDescription('Color star')
            ->setHelp('Parametr k označení barvy hvězdy');

        $this
            ->setName('points')
            ->setDescription('Points of star')
            ->setHelp('Parametr k označení počtu cípů hvězdy');

        $this
            ->setName('radius')
            ->setDescription('Radius of star')
            ->setHelp('Parametr k označení hodnoty od 0 do 1, která definuje "vykousnutí" cípů');

        $this
            ->setName('output')
            ->setDescription('Output of star')
            ->setHelp('Parametr k označení názvu souboru, do kterého se obrázek uloží');

        $this
            ->setName('bgColor')
            ->setDescription('Background color')
            ->setHelp('Nepovinný parametry k označení barvy pozadí, výchozí hodnota je bílá');

        $this
            ->setName('borderColor')
            ->setDescription('BorderColor')
            ->setHelp('Nepovinný parametry k označení barvy rámečku, pokud není zadaná, rámeček se nevykreslí');

        $this
            ->setName('borderWidth')
            ->setDescription('BorderWidth')
            ->setHelp('Nepovinný parametry k označení šířky rámečku v px, pokud není zadaná, rámeček se nevykreslí');

        // define command arguments
        $this->addArgument('width', InputArgument::REQUIRED, "Parametr k označení výšky a šířky výsledného obrázku")
            ->addArgument('color', InputArgument::REQUIRED, "Parametr k označení barvy hvězdy")
            ->addArgument('points', InputArgument::REQUIRED, "Parametr k označení počtu cípů hvězdy")
            ->addArgument('radius', InputArgument::REQUIRED, "Parametr k označení hodnoty od 0 do 1, která definuje vykousnutí cípů")
            ->addArgument('output', InputArgument::REQUIRED, "Parametr k označení názvu souboru, do kterého se obrázek uloží")
            ->addArgument('bgColor', InputArgument::OPTIONAL, "Nepovinný parametry k označení barvy pozadí, výchozí hodnota je bílá")
            ->addArgument('borderColor', InputArgument::OPTIONAL, "Nepovinný parametry k označení barvy rámečku, pokud není zadaná, rámeček se nevykreslí")
            ->addArgument('borderWidth', InputArgument::OPTIONAL, "Nepovinný parametry k označení šířky rámečku v px, pokud není zadaná, rámeček se nevykreslí");
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $width = $input->getArgument('width');
        $color = $input->getArgument('color');
        $points = $input->getArgument('points');
        $radius = $input->getArgument('radius');
        $output = $input->getArgument('output');
        $bgColor = $input->getArgument('bgColor');
        $borderColor = $input->getArgument('borderColor');
        $borderWidth = $input->getArgument('borderWidth');

//   validate width
        if (!is_numeric($width) || $width <= 0) {
            $output->writeln("Invalid width: \"{$width}\"");
            return Command::FAILURE;
        }

//   validate color
        if (!is_numeric($color) || $color <  65536 || $color > 16581375) {
            $output->writeln("Invalid color: \"{$color}\"");
            return Command::FAILURE;
        }

//   validate points
        if (!is_numeric($points) || $points <= 0) {
            $output->writeln("Invalid points: \"{$points}\"");
            return Command::FAILURE;
        }

//   validate radius
        if (!is_numeric($radius) || $radius < 0 || $radius > 1) {
            $output->writeln("Invalid radius: \"{$radius}\"");
            return Command::FAILURE;
        }

        $image = imagecreatetruecolor($width, $width);

        if ($borderWidth !== NULL && $borderColor !== NULL) {
            $color_str = str_split(dechex($bgColor), 2);
            $color_border = imagecolorallocate($image, hexdec($color_str[0]), hexdec($color_str[1]), hexdec($color_str[2]));
            imagefilledrectangle($image, 0, 0, $width, $width, $color_border);
            if ($bgColor !== NULL) {
                $color_str = str_split(dechex($bgColor), 2);
                $color_bg = imagecolorallocate($image, hexdec($color_str[0]), hexdec($color_str[1]), hexdec($color_str[2]));
//      Vyplnit pozadí uvnitř rámečku => vytvoří rámeček
                imagefilledrectangle($image, $borderWidth, $borderWidth, $width-$borderWidth, $width-$borderWidth, $color_bg);
            }
        }elseif ($bgColor !== NULL) {
            $color_str = str_split(dechex($bgColor), 2);
            $color_bg = imagecolorallocate($image, hexdec($color_str[0]), hexdec($color_str[1]), hexdec($color_str[2]));
//      Výplň pozadí
            imagefilledrectangle($image, 0, 0, $width, $width, $color_bg);
        }else {
        $color_bg = imagecolorallocate($image, 255, 255, 255);
//      Výplň pozadí
        imagefilledrectangle($image, 0, 0, $width, $width, $color_bg);}

// Smyčka pro výpočet vrcholů hvězdy
        function vypocet_vrcholu($width, $radius, $points, $Alpha): array
        {
            $coordinate = array();// Pole pro uložení souřadnic vrcholů hvězdy
            $x=$width/2; // Výpočet středu obrazovky pomocí x
            $y=$width*0.4; // Výpočet středu obrazovky pomocí y
            $R = $width*0.3;
            $radius = $radius*$R*2;
            for ($i = 1; $i < $points * 2 + 1; $i++) {
                if (!($i % 2)) // Pokud je splněna podmínka parity, následující vzorce
                {
                    $X = $x + $radius / 2 * cos($Alpha * M_PI / 180);
                    array_push($coordinate, $X);
                    $Y = $y - $radius / 2 * sin($Alpha * M_PI / 180);
                    array_push($coordinate, $Y);

                } else // Pokud není splněna podmínka parity, následující vzorce
                {
                    $X = $x + $R * cos($Alpha * M_PI / 180);
                    array_push($coordinate, $X);
                    $Y = $y - $R * sin($Alpha * M_PI / 180);
                    array_push($coordinate, $Y);
                }
                $Alpha = $Alpha + 180 / $points;
            }
            return $coordinate;
        }

//   Vytvoření samotné hvězdy ve formě mnohoúhelníku pomocí souřadnic z pole
        $color_star = str_split(dechex($color), 2);
        $color_of_star = imagecolorallocate($image, hexdec($color_star[0]), hexdec($color_star[1]), hexdec($color_star[2]));
        if ($points == 5) $arr_val = vypocet_vrcholu($width, $radius, $points, 17);
        if ($points == 6) $arr_val = vypocet_vrcholu($width, $radius, $points, 30);
        if ($points == 7) $arr_val = vypocet_vrcholu($width, $radius, $points, 38);
        if ($points == 8) $arr_val = vypocet_vrcholu($width, $radius, $points, 1);
        if ($points == 9) $arr_val = vypocet_vrcholu($width, $radius, $points, 10);
        if ($points == 10) $arr_val = vypocet_vrcholu($width, $radius, $points, 18);
        imagefilledpolygon($image, $arr_val, $points*2, $color_of_star);

//   Vložte text do obrázku
        $white = imagecolorallocate($image,255, 255, 255);
        $font = "fonts/arial.ttf";
        imagettftext($image, $width/30, 0, $width*0.4, $width*0.8, $white, $font, "points = $points");
        imagettftext($image, $width/30, 0, $width*0.4, $width*0.9, $white, $font, "radius = $radius");


//   Příprava na uložení obrázku v požadovaném formátu
        $inputImagePathParts = pathinfo($output);
        if ($inputImagePathParts['extension'] !== '.png') {
            $inputImagePathParts['extension'] = 'png';
        }
        $outputImage = $inputImagePathParts['dirname']
            . DIRECTORY_SEPARATOR
            . $inputImagePathParts['filename']
            . "-{$width}-{$width}."
            . $inputImagePathParts['extension'];
//   Uložit obrázek
//// ve tvaru png na dané cestě
        imagepng($image, $outputImage);

//   Vymazání obrazové proměnné
        imagedestroy($image);
        return 0;
    }
}
