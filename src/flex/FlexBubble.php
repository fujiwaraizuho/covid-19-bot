<?php

namespace src\flex;

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
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use src\LINEBot;

class FlexBubble
{
    private static $altText = "";
    private static $data = [];

    public function __construct(String $altText, \stdClass $data)
    {
        self::$altText = $altText;
        self::$data = $data;
    }


    public static function get(): FlexMessageBuilder
    {
        return FlexMessageBuilder::builder()
            ->setAltText(self::$altText)
            ->setContents(
                BubbleContainerBuilder::builder()
                    ->setBody(self::createBodyBlock(self::$data))
                    ->setFooter(self::createFooterBlock())
            );
    }


    private static function createBodyBlock(\stdClass $data): BoxComponentBuilder
    {
        $components = [];

        $components[] = TextComponentBuilder::builder()
            ->setText($data->type)
            ->setColor("#FF0000")
            ->setWeight(ComponentFontWeight::BOLD)
            ->setSize(ComponentFontSize::XL);

        $components[] = TextComponentBuilder::builder()
            ->setText($data->title)
            ->setWeight(ComponentFontWeight::REGULAR)
            ->setSize(ComponentFontSize::XL);

        $components[] = TextComponentBuilder::builder()
            ->setText($data->message)
            ->setSize(ComponentFontSize::SM)
            ->setMargin(ComponentMargin::SM);

        $components[] = SeparatorComponentBuilder::builder()
            ->setMargin(ComponentMargin::LG);

        $baseline = $data->baseline;

        $components[] = self::createBaseLineBoxBlock("確定日", $baseline[LINEBot::BASELINE_DATE]);
        $components[] = self::createBaseLineBoxBlock("年齢", $baseline[LINEBot::BASELINE_OLD]);
        $components[] = self::createBaseLineBoxBlock("性別", $baseline[LINEBot::BASELINE_GENDER]);
        $components[] = self::createBaseLineBoxBlock("居住地", $baseline[LINEBot::BASELINE_RECIDENCE]);
        $components[] = self::createBaseLineBoxBlock("濃厚接触者", $baseline[LINEBot::BASELINE_CLOSE_CONTACT]);
        $components[] = self::createBaseLineBoxBlock("濃厚接触者の状況", $baseline[LINEBot::BASELINE_CLOSE_CONTACT_STATUS], 5);

        $components[] = SeparatorComponentBuilder::builder()
            ->setMargin(ComponentSpacing::LG);

        return BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            ->setContents($components);
    }


    private static function createBaseLineBoxBlock(String $title, \stdClass $data, Int $titleFlex = 3, Int $dataFlex = 5): BoxComponentBuilder
    {
        $components = [];

        if ($data->bold) {
            $color = "#000000";
        } else {
            $color = "#666666";
        }

        $components[] = TextComponentBuilder::builder()
            ->setText($title)
            ->setSize(ComponentFontSize::SM)
            ->setColor("#AAAAAA")
            ->setFlex($titleFlex);

        $components[] = TextComponentBuilder::builder()
            ->setText($data->text)
            ->setSize(ComponentFontSize::SM)
            ->setColor($color)
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
