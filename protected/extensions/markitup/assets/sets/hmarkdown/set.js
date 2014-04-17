// -------------------------------------------------------------------
// markItUp!
// -------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// -------------------------------------------------------------------
// MarkDown tags example
// http://en.wikipedia.org/wiki/Markdown
// http://daringfireball.net/projects/markdown/
// -------------------------------------------------------------------
// Feel free to add more tags
// -------------------------------------------------------------------
mySettings = {
	previewParserPath:	'',
	onShiftEnter:		{keepDefault:false, openWith:'\n\n'},
	markupSet: [
		{name:'First Level Heading', key:'1', placeHolder:'Your title here...', closeWith:function(markItUp) { return miu.markdownTitle(markItUp, '=') } },
		{name:'Second Level Heading', key:'2', placeHolder:'Your title here...', closeWith:function(markItUp) { return miu.markdownTitle(markItUp, '-') } },
		{name:'Heading 3', key:'3', openWith:'### ', placeHolder:'Your title here...' },
		{name:'Heading 4', key:'4', openWith:'#### ', placeHolder:'Your title here...' },
		{name:'Heading 5', key:'5', openWith:'##### ', placeHolder:'Your title here...' },
		{name:'Heading 6', key:'6', openWith:'###### ', placeHolder:'Your title here...' },
		{separator:'---------------' },		
		{name:'Bold', key:'B', openWith:'**', closeWith:'**'},
		{name:'Italic', key:'I', openWith:'_', closeWith:'_'},
		{separator:'---------------' },
		{name:'Bulleted List', openWith:'- ' },
		{name:'Numeric List', openWith:function(markItUp) {
			return markItUp.line+'. ';
		}},
		{separator:'---------------' },
		{name:'Picture', key:'P', replaceWith:'![[![Alternative text]!]]([![Url:!:http://]!] "[![Title]!]")'},
		{name:'Upload Picture', key:'U', className: 'hmdImageUpload'},
		{name:'Link', key:'L', openWith:'[', closeWith:']([![Url:!:http://]!] "[![Title]!]")', placeHolder:'Your text to link here...' },
		{separator:'---------------'},	
		{name:'Quotes', openWith:'> '},
		{name:'Code Block / Code', openWith:'(!(\t|!|`)!)', closeWith:'(!(`)!)'},
		{separator:'---------------'},
		{name:'Preview', call:'preview', className:"preview"}
	]
}

// mIu nameSpace to avoid conflict.
miu = {
	markdownTitle: function(markItUp, char) {
		heading = '';
		n = $.trim(markItUp.selection||markItUp.placeHolder).length;
		for(i = 0; i < n; i++) {
			heading += char;
		}
		return '\n'+heading;
	},

	imagesIndex: -1,

	/**
	 * добавляет изрображение в поле загруженных изображений
	 * 
	 * @var jQuery $miu обьект textarea
	 * @var object imgData обьект с информацией об изображении. возможные поля обьекта: src, code
	 * @var boolean insert если true, то код изображения будет так же добавлен в текстовую область редактора
	 */
	pushImage: function($miu, imgData, insert)
	{
		// функция для вставки кода изображения в редактор
		var insertImage = function()
		{

			$.markItUp({target: $miu, openWith: '{{' + imgData.code + '}}'});
		};

		var $container = $miu.parent();
		if(!$container.hasClass('markItUpWithImages'))
		{
			// инициализируем режим с прикреплениями
			$container
				.append('<div id="' + $miu[0].id + 'Images" class="markItUpImagesContainer">')
				.addClass('markItUpWithImages');
		}

		$imagesContainer = $('#' + $miu[0].id + 'Images');

		if('id' in imgData)
			this.imagesIndex = Math.max(this.imagesIndex, imgData.id); // сохраняем максимальный id картинки
		else
			++this.imagesIndex;

		imgData.code = 'IMAGE_' + this.imagesIndex;

		imgData.name = imgData.src.slice(imgData.src.lastIndexOf('/') + 1); // имя файла изображения

		$('<div>')
			.html('<img src="' + imgData.src + '" /> <p>Код изображения: {{<b>' + imgData.code + '</b>}}</p><br><p style="float:right;"><a href="" class="icon_edit"></a><a href="" class="icon_delete"></a></p>')
			.click(insertImage)
			.appendTo($imagesContainer);

		if (insert)
			insertImage();
	},
}
// TODO: возможность указать титл и альт
// TODO: возможность указать центрирование
// TODO: создание полей формы с данными картинки
// TODO: кнопки "удалить" и "редактировать"