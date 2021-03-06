<div class="productsearch-wrapper">
	<h4 class="header smaller lighter purple"><i class="icon-search"></i> Search for available products here:  </h4>
	{{ Form::open(array('route'=>'searchproduct', 'id'=>'myform', 'class'=>'form-horizontal', 'style'=>'position:relative;' )) }}

	<div class="control-group row-fluid">
		<div class="input-prepend span12">
			
		{{Form::text('products', '', array( 'class'=>'span8 input-block-level searchproduct', 'id'=>'searchproduct', 'autocomplete'=>'off'))}}

			<div name="salemode" data-options="{{$str_modes}}" data-attr="validate='required'" rel="selectoption" setvaluefrom="text"></div>
		</div>
	</div>

	<div class="control qf_radio">
		<h3 class="lighter green"> Search item by: 
			<label for="by_name" class="inline">
				<input type="radio" name="qf" id="by_name" value="name" checked="checked"/>
			 	<span class="lbl muted"> Name </span>
			</label>
				&nbsp; &nbsp; &nbsp; &nbsp; 
			<label for="by_barcodeid" class="inline">
				<input type="radio" name="qf" id="by_barcodeid" value="barcodeid"/>
			 	<span class="lbl muted"> Barcode </span>
			</label>
		</h3>
	</div>

	{{Form::close()}}
</div>

<div id="suspended_wrapper" style="position: relative; padding:10px; min-height: 285px;">
	<h3 class="header smaller lighter blue">
	<i class="icon-save"></i>Suspended Transactions:
	</h3>

	{{--tt(Cartsession::getSuspend())--}}

	@if( !empty(Cartsession::getSuspend()) )
	<div class="row-fluid">
		<div class="span12">
			<table class="table table-striped table-bordered table-hover">
				<tbody>
				<?php $count = 1; ?>
				@foreach( array_reverse(Cartsession::getSuspend()) as $key => $content )
				<tr>
					<td>
						<div class="bolder">
							{{$count++}}
						</div>
					</td>
					<td>
						<button class="btn btn-mini btn-danger remove-suspended" data-removesuspended-url="{{URL::route('removesuspended', ['key' => $key])}}">
							<i class="icon-trash bigger-120"></i>
						</button>
					</td>
					<td>
						<div  style="font-size:15px">
							{{currency()}}{{format_money($content['totalamounttendered'])}}k
						</div>
					</td>
					<td>
						
						<div class="btn-group">
							<button class="btn btn-mini btn-success preview-suspend" data-previewsuspended-url="{{URL::route('previewsuspended', ['key' => $key])}}">
								<i class="icon-eye-open bigger-120"></i>
							</button>
							<button class="btn btn-mini btn-info resume-cart" data-resumecart-url="{{URL::route('resumecart', ['key' => $key])}}">
								<i class="icon-refresh bigger-120"></i>
							</button>
						</div>

					</td>
				</tr>
				@endforeach
				</tbody>
			</table>
		</div>
	</div>
	@endif

</div>


<script>
	$(function(){

		$('#suspended_wrapper').on('click', '.resume-cart', function(e){
			e.preventDefault();
			var urlx = $(this).attr('data-resumecart-url');

			var cartItem = Number($('.total_items_in_cart').text());

			if( cartItem > 0 ){
				var wat = confirm('Do you want to overwrite current cart contents');

				if( wat == false){ return false; }
			}

			window.location.href = urlx;
			return false;
		});		

		$('#suspended_wrapper').on('click', '.remove-suspended', function(e){
			e.preventDefault();
			var urlx = $(this).attr('data-removesuspended-url');
			window.location.href = urlx;
			return false;
		});

		var preview_suspend = function (e){
			e.preventDefault();
			e.stopPropagation();
			var $that = $(this);
			var urlx = $(this).attr('data-previewsuspended-url');

			$that.off('click.previewsuspended', preview_suspend);

			$.get(urlx, function (data){
				cloneModalbox($('#myModal'))
				.css({'width':'900px'})
				.centerModal()
				.find('.modal-body').html(data)
				.end()
				.find('.modal-header h3')
				.text('Preview suspended cart')
				.end()
				.find('.modal-footer [data-ref="submit-form"]')
				.hide()
				.end()
				.modal({
					backdrop: 'static'
				});
				//Re-bind click event
				//$that.on('click.previewsuspended', preview_suspend);	
			});
		}

		$('#suspended_wrapper').on('click.previewsuspended', '.preview-suspend', preview_suspend);

		//
		$('[rel="selectoption"]').bootstrap_selectoptions({
			//makeDefaultDuplicate: true,
			style: 'width:122px; overflow:hidden',
			hideSelected: true,
			btnCls : 'btn-success'
		});

		//This would auto suggest the product you're looking for

		//$('#searchproduct').on('keydown', function(){
		//	var $shat = $(this);

		$('#searchproduct').ttypeahead({
			url: "{{URL::route('searchproduct')}}",
			updaterTemplate: "squeen_skin(item, map)",
			highlighterTemplate: "typhoon_skin(item, map, query)",
			csrf_token: $('input[name="_token"]').val(),
			barcodeLen:10
		});
		//});

		//This would prevent the PRODUCT search from responding to ENTERKEY
		//VERY IMPORTANT CODE FOR STABILITY
		$('#searchproduct').keydown(function(e){ 
			if( e.which == 13 ){ 
				e.preventDefault(); 
			}
		});

		$('#searchproduct').trigger('focus');

	});

	//This is a customized function for TYPE HEAD highlighter
	function typhoon_skin(item, map, query){
		if(item === ''){ return false; }
		var regex, discountlabel, htmlTemplate;

		htmlTemplate = '<div class="row-fluid">';
		htmlTemplate += '<div class="span12">';

 		if(map[item].quantity <= 0 && map[item].categories.type != 'service'){
 			htmlTemplate += '<div class="span12 content-product" style="overflow: hidden; word-wrap:break-word; min-height:20px">';

 			htmlTemplate += '<div class="pull-left" style="white-space:nowrap">';
			htmlTemplate += '<span class="label label-large label-default">' + map[item].brand.name.capitalize() + '</span>';
			htmlTemplate += ' <span class="label label-large label-default">'+ map[item].categories.name.capitalize() +'</span>';
			htmlTemplate += '</div>';
			htmlTemplate += '<div> ' + map[item].name.capitalize() + ': is not available</div>';
			htmlTemplate += '</div>';
 		}else{
 			htmlTemplate += '<div class="span7 content-product" style="overflow: hidden; word-wrap:break-word; min-height:20px">';
			regex = new RegExp( '(' + query + ')', 'i' );
			discountlabel ='', discount='';
			htmlTemplate += map[item].name.capitalize().replace(regex,"<span class='red'>$1</span>" );
			htmlTemplate += '</div>';
				if( map[item].categories.type === 'product'){
					htmlTemplate += '<div class="span5"><span class="badge badge-warning">'+ map[item].quantity +'</span> Qty in stock</div><br>';
				}
			htmlTemplate += '<div class="pull-left" style="white-space:nowrap">';
			htmlTemplate += '<span class="label label-large label-purple">' + map[item].brand.name.capitalize() + '</span>';

			//If discount is available
			if( map[item].discount > 0 ){
				discountlabel = 'label label-large label-yellow';
				discount = ' <span class="label label-large label-inverse">'+ map[item].discount +'%</span>';
			}

			htmlTemplate += ' <span class="label label-large label-info">'+ map[item].categories.name.capitalize() +'</span>';
			htmlTemplate += ' <span class="'+ discountlabel +'">{{currency()}}' + format_money(map[item].price - (map[item].price * map[item].discount/100),2) +'k</span>'+ discount;
			htmlTemplate += '</div>';
		}

			htmlTemplate += '</div>';
			htmlTemplate += '</div>';
		return htmlTemplate;
	}

	function squeen_skin(item, map){
		var HTMLBODY, $finderTR, discount, productname, found = false, mode, isHidden='hidden', discounted_price='', crossPrice='';
			productname =  map[item].name.capitalize();
			mode = $('input[name="salemode"]').val();

			$finderTR = $('.cart-place').find('tr[idx='+map[item].id+']');
			//_debug($('.cart-place').find('tr[idx='+map[item].id+']').text());

				//We first check if the Item is listed
				if( $finderTR.text() !== ''){

					//Then we check if the Listed item with its salemode is not listed
					$('.cart-place').find('tr[idx='+map[item].id+'] td.salesmodename').each(function(){

						//If listed we assign the "found" variable to the salemode name
						if($(this).text() === mode){
							var $currentInput = $finderTR.find('td.quantity input.quantity_input');
							var updateQty = Number($currentInput.val()) + 1;
							$currentInput.val(updateQty);
							updateCartInfo(null, $finderTR, "{{URL::route('autoSaveCart')}}");
							found = mode;
						}
					});

					//If variable "found" is not false. It means the chosen item is already listed
					if( found !== false ){
						//bootbox.alert('[ ' + found + ' ][ ' + productname + ' ] is already in the cart. You can change quantity in the cart');
					}else{
						//Else we list the chosen item
						getThem();
					}

					/*** WE ADD THE QUANTITY ON THE FLY ***/

			 	}else{
			 		//We list the chosen item as its never existed in the lists
			 		getThem();
			 	}


			 	function getThem(){
			 		discount = map[item].price - (map[item].price * map[item].discount/100);

				    HTMLBODY  = '<tr idx="'+ map[item].id + '">';
				    //HTMLBODY += '<td class="brandname">'+ map[item].brand.name.capitalize() +'</td>';
				     HTMLBODY += '<td class="quantity center" width="7%"><input type="text" autocomplete="off" value="1" class="quantity_input" /></td>';
				    HTMLBODY += '<td class="productname" width="40%">'+ map[item].brand.name.capitalize() +'/'+productname +'</td>';

				    if( map[item].discount > 0 ){
				    	isHidden = '';
				    	discounted_price = format_money(map[item].discounted_price);
				    	crossPrice = 'oldUnitPrice';
				    }

				    HTMLBODY += '<td class="unitprice" width="15%">';

		@if(User::permitted( 'role.stockmanager'))
				    HTMLBODY += '<a href="#" class="light-green cog_unitprice" data-pk='+map[item].id+'><i class="icon-cog"></i></a> ';
		@endif
				    HTMLBODY +='{{currency()}}<span class="unitprice '+crossPrice+'">'+ format_money(map[item].price, 2) +'</span>k';
				    HTMLBODY +='<span class="'+isHidden+'"><br>';

		@if(User::permitted( 'role.stockmanager'))
				    HTMLBODY +='<a href="#" class="red remove_discount_price_on_fly"><i class="icon-minus-sign"></i></a> ';
		@endif

				    HTMLBODY += '{{currency()}}<span class="discount_price_on_fly">'+discounted_price+'</span>k</span></td>';

				    HTMLBODY += '<td class="discount" width="8%"><span class="discount" data-title="Enter discount">'+ map[item].discount +'</span>%</td>';

				    HTMLBODY += '<td class="total" width="17%">{{currency()}}<span class="total">'+ format_money(discount, 2) +'</span>k</td>';
				    //mode = ( map[item].categories.type === 'service' ) ? 'Services' : mode;
				    HTMLBODY += '<td class="salesmodename" width="10%">'+ mode +'</td>';
				    HTMLBODY += '<td class="action-buttons"><a href="::;" class="removeProduct red"><i class="icon-remove"></i></a></td>';
				    //Hidden TDs
				    HTMLBODY += '<td class="productcat hide">'+ map[item].categories.name +'</td>';
				    HTMLBODY += '<td class="quantityinstock hide">'+ map[item].quantity +'</td>';
				    HTMLBODY += '<td class="costprice hide">'+ map[item].costprice +'</td>';
				    HTMLBODY += '<td class="cat_type hide">'+ map[item].categories.type +'</td>';
				    HTMLBODY += '</tr>';
					
					//Appending the product to the already avaliable lists 
			     	$('.cart-place tbody').append(HTMLBODY);

			     	//Getting total amount
			     	var subtotalamount = getAmount() + discount;
			     	$('.subtotalamount').text(format_money(subtotalamount, 2));

			     	//We call a function to calculate the Vat and the Totalamount. RETURN JSON OBJECT
			     	//vatAndTotalAmount [ function in g56_function.js file ]
			     	var result = vatAndTotalAmount( subtotalamount );

			     	//Getting the total amount tendered after overall discount
			     	$('.totalamounttendered').text(format_money( getAmountTendered(), 2 ) );

			     	//Lets update the Item in our Cart
					updateCartAndActivateCheckoutButton();

			     	//Save the current presence of the cart
			     	HTMLBODY += '||'+ map[item].id+'||'+ mode;
			     	saveCart("{{URL::route('autoSaveCart')}}", { 
			     			cart_content : HTMLBODY, 
			     			subtotalamount : subtotalamount, 
			     			vat:result.vat, 
			     			totalamount :result.totalamount,  
			     			cart_quantity: $('.total_items_in_cart').text(), 
			     			enteroveralldiscount: $('.enteroveralldiscount input').val(), 
			     			totalamounttendered: unformat_money($('.totalamounttendered').text())
			     		});
			 	}

		//return map[item].name;
	}
</script>