!function($){function RichAutocomplete(element,options){this.element=element,this.options=options,this.items=this.options.items.slice(),this.filteredItems=this.items.slice(),this.init(),this.bindEvents(),!1===this.options.paging?this.updateList():(this.currentPage=0,this.loading=!1,this.allItemsLoaded=!1,this.debounce=null,this.loadPage(0))}RichAutocomplete.prototype.init=function(){if("INPUT"!==this.element[0].nodeName)throw"Rich Autocomplete - Expected <input> but instead got <"+this.element[0].nodeName.toLowerCase()+">";this.element.wrap('<div class="rich-autocomplete"></div>'),this.list=$('<ul class="rich-autocomplete-list"></ul>'),this.list.css("max-height",this.options.maxHeight+"px"),this.list.hide(),this.container=this.element.parent(),this.container.append(this.list);this.spinner=$('<div class="spinner-container"><div class="spinner"><div class="spinner-node node1"></div><div class="spinner-node node2"></div><div class="spinner-node node3"></div><div class="spinner-node node4"></div><div class="spinner-node node5"></div><div class="spinner-node node6"></div><div class="spinner-node node7"></div><div class="spinner-node node8"></div></div></div>'),this.spinner.hide(),this.container.append(this.spinner)},RichAutocomplete.prototype.bindEvents=function(){var context=this;this.element.focus(function(event){context.showList.apply(context,[event])}),this.element.blur(function(event){context.hideList.apply(context,[event])}),this.element.keyup(function(event){38!==event.keyCode&&40!==event.keyCode&&context.filterResults.apply(context,[event])}),this.element.keydown(function(event){38===event.keyCode&&context.highlightUp.apply(context,[event]),40===event.keyCode&&context.highlightDown.apply(context,[event]),13===event.keyCode&&context.selectHighlighted.apply(context,[event])}),this.list.scroll(function(event){if(!1!==context.options.paging&&!0!==context.loading&&!0!==context.allItemsLoaded){var scrollPosition=context.list.scrollTop()+context.list.height();context.list[0].scrollHeight-20<scrollPosition&&context.loadNextPage.apply(context)}})},RichAutocomplete.prototype.showList=function(){this.list.show()},RichAutocomplete.prototype.hideList=function(event){this.list.hide(),this.list.find(".highlighted").removeClass("highlighted")},RichAutocomplete.prototype.filterResults=function(event){var context=this,searchTerm=this.element.val();!1===this.options.paging?(this.filteredItems=this.options.filter(this.items,searchTerm),this.updateList()):(this.debounce&&clearTimeout(this.debounce),this.debounce=setTimeout(function(){context.currentPage=0,context.allItemsLoaded=!1,context.loading=!1,context.spinner.hide(),context.loadPage(0)},""===searchTerm?0:this.options.debounce))},RichAutocomplete.prototype.loadPage=function(pageNumber){var context=this;if(!0!==this.loading&&(0===pageNumber||!0!==this.allItemsLoaded)){this.loading=!0,this.options.showSpinner&&this.spinner.show(),0===pageNumber&&(this.filteredItems=[],this.allItemsLoaded=!1);var searchTerm=this.element.val(),nextPage=this.options.loadPage(searchTerm,pageNumber,this.options.pageSize);nextPage.promise?nextPage.done(function(result){context.filteredItems=result,context.updateDynamicList.apply(context),context.spinner.hide(),context.loading=!1,(0===result.length||result.length<context.pageSize)&&(context.allItemsLoaded=!0)}):(this.filteredItems=this.filteredItems.concat(nextPage),this.updateDynamicList(),this.spinner.hide(),this.loading=!1,(0===nextPage.length||nextPage.length<this.pageSize)&&(this.allItemsLoaded=!0))}},RichAutocomplete.prototype.loadNextPage=function(){!0!==this.loading&&!0!==this.allItemsLoaded&&this.loadPage(++this.currentPage)},RichAutocomplete.prototype.updateDynamicList=function(){var highlightedData=this.list.find(".highlighted").first().data("item-data");(this.updateList(),highlightedData)&&this.list.find(".rich-autocomplete-list-item").each(function(index,element){if($(element).data("item-data")===highlightedData)return $(element).addClass("highlighted"),!1})},RichAutocomplete.prototype.updateList=function(){var context=this;if(this.list.empty(),0===this.filteredItems.length){var emptyItem=$('<li class="rich-autocomplete-list-item-empty"></li>');return emptyItem.append($(this.options.emptyRender())),void this.list.append(emptyItem)}for(var selectItem=function(event){var itemData=$(this).data("item-data");context.selectItem.apply(context,[itemData])},hoverItem=function(event){context.hoverItem.apply(context,[$(this)])},unhoverItem=function(event){context.unhoverItem.apply(context,[$(this)])},idx=0;idx<this.filteredItems.length;idx++){var listItem=$('<li class="rich-autocomplete-list-item" index="'+idx+'"></li>');listItem.append($(this.options.render(this.filteredItems[idx]))),listItem.data("item-data",this.filteredItems[idx]),listItem.mousedown(selectItem),listItem.mouseover(hoverItem),listItem.mouseout(unhoverItem),this.list.append(listItem)}},RichAutocomplete.prototype.hoverItem=function(item){this.list.find(".highlighted").removeClass("highlighted"),item.addClass("highlighted")},RichAutocomplete.prototype.unhoverItem=function(item){item.removeClass("highlighted")},RichAutocomplete.prototype.selectItem=function(item){var itemText=this.options.extractText(item);this.element.val(itemText),this.options.select(item),this.filterResults()},RichAutocomplete.prototype.selectHighlighted=function(){var highlighted=this.list.find(".highlighted");if(0!==highlighted.length){var itemData=highlighted.first().data("item-data");this.selectItem(itemData),this.hideList()}},RichAutocomplete.prototype.highlightUp=function(){var highlighted=this.list.find(".highlighted");if(0===highlighted.length)this.hideList();else if(this.listVisible()&&0<this.filteredItems.length){var listItems=this.list.find(".rich-autocomplete-list-item"),currentIndex=+highlighted.first().attr("index"),minIndex=+listItems.first().attr("index");if(minIndex<currentIndex){var previousSibling=highlighted.first().prev(".rich-autocomplete-list-item");if(0===previousSibling.length)return;highlighted.removeClass("highlighted"),previousSibling.addClass("highlighted");var listHeight=this.list.height(),scrollTop=this.list.scrollTop(),scrollBottom=scrollTop+listHeight,highlightTop=previousSibling.position().top+scrollTop,highlightBottom=highlightTop+previousSibling.outerHeight();scrollBottom<=highlightBottom?this.list.scrollTop(0<highlightBottom-listHeight?highlightBottom-listHeight:0):highlightTop<scrollTop&&this.list.scrollTop(highlightTop)}else currentIndex===minIndex&&this.hideList()}},RichAutocomplete.prototype.highlightDown=function(){var listHeight,scrollTop,highlightTop,highlightBottom,highlighted=this.list.find(".highlighted");if(0===highlighted.length){this.listVisible()||this.showList();this.list.find(".rich-autocomplete-list-item").first().addClass("highlighted");this.list.scrollTop(0)}else if(this.listVisible()){var listItems=this.list.find(".rich-autocomplete-list-item");if(+highlighted.first().attr("index")<+listItems.last().attr("index")){var nextSibling=highlighted.first().next(".rich-autocomplete-list-item");if(0===nextSibling.length)return;highlighted.removeClass("highlighted"),nextSibling.addClass("highlighted"),listHeight=this.list.height(),(scrollTop=this.list.scrollTop())+listHeight<=(highlightBottom=(highlightTop=nextSibling.position().top+scrollTop)+nextSibling.outerHeight())?this.list.scrollTop(0<highlightBottom-listHeight?highlightBottom-listHeight:0):highlightTop<scrollTop&&this.list.scrollTop(highlightTop)}}},RichAutocomplete.prototype.listVisible=function(){return"none"!==this.list[0].style.display},$.fn.richAutocomplete=function(options){options=$.extend({maxHeight:200,items:[],paging:!1,pageSize:0,showSpinner:!0,debounce:500,extractText:function(item){return item},filter:function(items,searchTerm){return items.filter(function(item){return-1!==item.toLowerCase().indexOf(searchTerm.toLowerCase())})},render:function(item){return"<p>"+item+"</p>"},emptyRender:function(){return"<p>No Matches Found...</p>"},select:function(item){},loadPage:function(searchTerm,pageNumber){return[]}},options),$(this).data("rich-autocomplete",new RichAutocomplete(this,options))}}(jQuery);