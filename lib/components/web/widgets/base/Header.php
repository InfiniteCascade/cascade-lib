<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web\widgets\base;

use cascade\components\web\widgets\Widget;
use canis\helpers\Html;
use Yii;

/**
 * Header [[@doctodo class_description:cascade\components\web\widgets\base\Header]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Header extends Widget
{
    /**
     * @inheritdoc
     */
    public function generateContent()
    {
        if (!isset(Yii::$app->request->object)) {
            return false;
        }
        $object = Yii::$app->request->object;
        $content = [];
        $content[] = Html::beginTag('div', ['class' => 'well ic-masthead']);
        if (Yii::$app->request->previousObject) {
            $content[] = Html::beginTag('div', ['class' => 'ic-object-previous']);
            $content[] = Html::tag('span', '', ['class' => 'fa fa-reply']) . '&nbsp; ' . Html::a('Go back to <em>' . Yii::$app->request->previousObject->descriptor . '</em>', Yii::$app->request->previousObject->getUrl('view', [], false));
            $content[] = Html::endTag('div');
        }
        if ($object->getBehavior('Photo')) {
            $photoHtmlOptions = [];
            $img = $object->getPhotoUrl(200);
            if ($img) {
                Html::addCssClass($photoHtmlOptions, 'ic-object-photo ic-object-photo-image');
                $photoHtmlOptions['style'] = 'background-image: url("' . $img . '");';
            } else {
                Html::addCssClass($photoHtmlOptions, 'ic-object-photo ic-object-photo-icon ' . $object->objectType->icon);
            }
            $photoContent = '';
            $content[] = Html::tag('div', $photoContent, $photoHtmlOptions);
        }
        $content[] = Html::beginTag('div', ['class' => 'ic-object-header']);
        $content[] = $object->descriptor;
        $objectAccess = $object->objectAccess;
        $objectVisibility = $objectAccess->visibility;
        $content[] = Html::endTag('div');

        if ($object->archived) {
            $content[] = Html::tag('div', Html::tag('span', 'Archived', ['class' => 'label label-warning']), ['class' => 'center-block center-text']);
        }

        $menu = [];
        $familiarty = $object->getFamiliarity();
        $startWatchingItem = [
            'label' => Html::tag('span', '', ['class' => 'fa fa-eye']) . ' Watch',
            'url' => $object->getUrl('watch'),
            'options' => [
                'title' => 'Watch and get notified with changes',
                'data-handler' => 'background',
                'class' => 'watch-link',
                'data-watch-task' => 'start',
            ],
        ];
        $stopWatchingItem = [
            'label' => Html::tag('span', '', ['class' => 'fa fa-check']) . ' Watching',
            'url' => $object->getUrl('watch', ['stop' => 1]),
            'options' => [
                'title' => 'Stop receiving change notifications',
                'data-handler' => 'background',
                'data-watch-task' => 'stop',
                'class' => 'watch-link',
            ],
        ];
        $accessManageUrl = $object->getUrl('access');
        $startWatchingItem['companion'] = $stopWatchingItem;
        $stopWatchingItem['companion'] = $startWatchingItem;
        if (empty($familiarty->watching)) {
            $menu[] = $startWatchingItem;
        } else {
            $menu[] = $stopWatchingItem;
        }

        if ($object->can('update')) {
            $menu[] = [
                'label' => Html::tag('span', '', ['class' => 'fa fa-wrench']) . ' Update',
                'url' => $object->getUrl('update'),
                'options' => ['title' => 'Update', 'data-handler' => 'background'],
            ];
        }
        if ($object->can('delete') || $object->can('archive')) {
            $label = 'Delete';
            $icon = 'fa-trash-o';
            if ($object->can('archive')) {
                $icon = 'fa-archive';
                if ($object->archived) {
                    $label = 'Unarchive';
                } else {
                    $label = 'Archive';
                }
            }
            $menu[] = [
                'label' => Html::tag('span', '', ['class' => 'fa ' . $icon]) . ' ' . $label,
                'url' => $object->getUrl('delete'),
                'options' => ['title' => $label, 'data-handler' => 'background'],
            ];
        }

        if ($objectVisibility === 'public') {
            $accessIcon = 'fa fa-globe';
            $accessTitle = $object->objectType->title->upperSingular . ' is public';
            $accessLabel = 'Public';
        } elseif ($objectVisibility === 'shared') {
            $accessIcon = 'fa fa-rss';
            $accessTitle = $object->objectType->title->upperSingular . ' is shared';
            $accessLabel = 'Shared';
        } elseif ($objectVisibility === 'internal') {
            $accessIcon = 'fa fa-building-o';
            $accessTitle = $object->objectType->title->upperSingular . ' is shared internally';
            $accessLabel = 'Internal';
        } elseif ($objectVisibility === 'admins') {
            $accessIcon = 'fa fa-lock';
            $accessTitle = $object->objectType->title->upperSingular . ' is shared only with administrators';
            $accessLabel = 'Administrators';
        } else {
            $accessIcon = 'fa fa-user';
            $accessTitle = $object->objectType->title->upperSingular . ' is private';
            $accessLabel = 'Private';
        }

        if ($object->can('manageAccess')) {
        }

        $menu[] = [
            'label' => Html::tag('span', '', ['class' => $accessIcon]) . ' ' . $accessLabel,
            'url' => $accessManageUrl,
            'options' => ['title' => $accessTitle, 'data-handler' => 'background'],
        ];
        $menu[] = [
            'label' => Html::tag('span', '', ['class' => 'fa fa-history']) . ' Activity',
            'url' => $object->getUrl('activity'),
            'options' => ['title' => 'View Activity', 'data-handler' => 'background'],
        ];

        if (!empty($menu)) {
            $content[] = Html::beginTag('div', ['class' => 'ic-object-menu columns-' . count($menu)]);
            $content[] = Html::beginTag('ul', ['class' => 'clearfix']);
            $menuCount = 0;
            foreach ($menu as $item) {
                $menuCount++;
                if (!isset($item['options'])) {
                    $item['options'] = [];
                }
                $companionContent = '';
                if (isset($item['companion'])) {
                    $companion = $item['companion'];
                    if (!isset($companion['options'])) {
                        $companion['options'] = [];
                    }
                    Html::addCssClass($companion['options'], 'hidden');
                    $companionContent = Html::a($companion['label'], $companion['url'], $companion['options']);
                }
                $content[] = Html::tag('li', $companionContent . Html::a($item['label'], $item['url'], $item['options']), ['class' => 'item-' . $menuCount]);
            }
            $content[] = Html::endTag('ul');
            $content[] = Html::endTag('div');
        }
        $content[] = Html::endTag('div');

        return implode($content);
    }
}
