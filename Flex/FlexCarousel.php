<?php

namespace Flex;

use LINE\LINEBot\Constant\Flex\ComponentButtonHeight;
use LINE\LINEBot\Constant\Flex\ComponentFontSize;
use LINE\LINEBot\Constant\Flex\ComponentFontWeight;
use LINE\LINEBot\Constant\Flex\ComponentLayout;
use LINE\LINEBot\Constant\Flex\ComponentMargin;
use LINE\LINEBot\Constant\Flex\ComponentSpacing;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\SeparatorComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

class FlexCarousel
{
    private static $altText = "";
    private static $datas = [];

    public function __construct(String $altText, array $datas)
    {
        self::$altText = $altText;
        self::$datas = $datas;
    }


    public static function get(): FlexMessageBuilder
    {
        return FlexMessageBuilder::builder()
            ->setAltText(self::$altText)
            ->setContents(new CarouselContainerBuilder(self::createBubbles()));
    }


    private static function createBubbles(): array
    {
        $bubbles = [];

        foreach (self::$datas as $data) {
            $bubbles[] = BubbleContainerBuilder::builder()
                ->setBody(self::createBodyBlock($data))
                ->setFooter(self::createFooterBlock());
        }

        return $bubbles;
    }


    private static function createBodyBlock(array $datas): BoxComponentBuilder
    {
        $components = [];

        $components[] = TextComponentBuilder::builder()
            ->setText($datas["type"])
            ->setColor("#FF0000")
            ->setWeight(ComponentFontWeight::BOLD)
            ->setSize(ComponentFontSize::XL);

        $components[] = TextComponentBuilder::builder()
            ->setText($datas["title"])
            ->setWeight(ComponentFontWeight::REGULAR)
            ->setSize(ComponentFontSize::XL);

        $components[] = TextComponentBuilder::builder()
            ->setText($datas["message"])
            ->setSize(ComponentFontSize::SM)
            ->setMargin(ComponentMargin::SM);

        $components[] = SeparatorComponentBuilder::builder()
            ->setMargin(ComponentMargin::LG);

        $components[] = self::createBaseLineBoxBlock("確定日", $datas["baseline"]["date"]);
        $components[] = self::createBaseLineBoxBlock("年齢", $datas["baseline"]["old"]);
        $components[] = self::createBaseLineBoxBlock("性別", $datas["baseline"]["gender"]);
        $components[] = self::createBaseLineBoxBlock("居住地", $datas["baseline"]["residence"]);
        $components[] = self::createBaseLineBoxBlock("濃厚接触者", $datas["baseline"]["close_contact"]);
        $components[] = self::createBaseLineBoxBlock("濃厚接触者の状況", $datas["baseline"]["close_contact_status"], 5);

        $components[] = SeparatorComponentBuilder::builder()
            ->setMargin(ComponentSpacing::LG);

        return BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            ->setContents($components);
    }


    private static function createBaseLineBoxBlock(String $title, String $data, Int $titleFlex = 3, Int $dataFlex = 5): BoxComponentBuilder
    {
        $components = [];

        $components[] = TextComponentBuilder::builder()
            ->setText($title)
            ->setSize(ComponentFontSize::SM)
            ->setColor("#AAAAAA")
            ->setFlex($titleFlex);

        $components[] = TextComponentBuilder::builder()
            ->setText($data)
            ->setSize(ComponentFontSize::SM)
            ->setColor("#666666")
            ->setFlex($dataFlex)
            ->setWrap(true);

        return BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::BASELINE)
            ->setMargin(ComponentMargin::LG)
            ->setSpacing(ComponentSpacing::MD)
            ->setContents($components);
    }


    private static function createFooterBlock(): BoxComponentBuilder
    {
        $button = ButtonComponentBuilder::builder()
            ->setHeight(ComponentButtonHeight::SM)
            ->setAction(
                new UriTemplateActionBuilder(
                    "厚生労働省 HP",
                    "https://www.mhlw.go.jp/stf/seisakunitsuite/bunya/0000164708_00001.html"
                )
            );

        return BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            ->setContents([
                $button
            ]);
    }
}
