# AWZ: Html в Rich Content (awz.richcontent)

### [Установка модуля](https://github.com/zahalski/awz.richcontent/tree/main/docs/install.md)

<!-- desc-start -->

## Описание
Модуль для создания разметки Rich Content. Данный формат используется в описании товаров на ozon.ru.

Модуль позволяет создавать разметку Rich Content из описания товара:

1) Автоматически на обработчике
2) Пошаговым генератором вручную
3) Программно, используя API модуля

Описание формата на ozon: [Rich Content Json](https://rich-content.ozon.ru/docs)

**Поддерживаемые редакции CMS Битрикс:**<br>
«Старт», «Стандарт», «Малый бизнес», «Бизнес», «Корпоративный портал», «Энтерпрайз», «Интернет-магазин + CRM»

<!-- desc-end -->

## Документация
<!-- dev-start -->
### Awz\RichContent\Helper::getRichText

<em>получает Rich Content из HTML</em>

| Параметр                            |                    | Описание                  |
|-------------------------------------|--------------------|---------------------------|
| $desc `string`                      | Обязательно        | html для преобразования   |
| $mask `Awz\RichContent\Right\bMask` | null               | маска разрешений контента |

Возвращает строку с Rich Content разметкой `json`

#### пример 1

```php
if(\Bitrix\Main\Loader::includeModule('awz.richcontent')){
    $mask = new \Awz\RichContent\Right\bMask(\Awz\RichContent\Helper::ALLOW_TEXT | \Awz\RichContent\Helper::ALLOW_IMAGE);
    $html = '<div class="test"><p>Описание товара для <a href="https://ozon.ru">ozon.ru</a></p></div>';
	$richJson = \Awz\RichContent\Helper::getRichText($html, $mask);
	//{"content":[{"widgetName":"raTextBlock","text":{"size":"size2","color":"color1","content":["Описание товара для ozon.ru"]}}],"version":0.29999999999999999}
}
```
<!-- dev-end -->


<!-- cl-start -->
## История версий

https://github.com/zahalski/awz.richcontent/blob/master/CHANGELOG.md

<!-- cl-end -->
