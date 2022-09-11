/*
** Implementing into webpage:
** 		1. Ensure scripts are loaded in order: jQuery, signature-pad.js, form-sig-pad.js
**		2. Ensure that the form-sig-pad.css is loaded
**		3. Any button to launch the modal MUST have the "data-tgt={img element id}"
**		4. Insert the SignaturePad html (below) within your html document
**		5. Each Signature image must have a hidden input after it with an id prefix of -inp
**
** SignaturePad html:
** <div id="sig-pad-modal"></a><canvas id="sig-pad"></canvas><div id="sig-pad-btns"><a href="#" id="sig-accept" class="btn green pull-left">Submit</a><div class="btn-group pull-right"><a href="#" class="btn yellow"  id="sig-clear">Clear</a><a href="#" class="btn red"  id="sig-cancel">Cancel</a></div></div></div>
**
** SignatureImage html example:
** <img id="sig-img-1" class="sig-img" /><input type="hidden" name="tech-sig" id="sig-img-1-inp"/>
**
**
** SignatureButton html:
** must have the "sig-btn" class & "data-tgt" attr, E.g:
** 		<div class="btn btn-primary sig-btn" data-tgt="sig-img-1">Insert Signature</div>
**		*where sig-img-1 is the respective img elements' id
**
**
** JSlint error fixes
*/
/*global SignaturePad */

$(document).ready(function(){
		"use strict";
	if($("#sig-pad").length){
		var $sigModal  = $("#md-sig-pad"),//-modal"),
			$sigPadEle = $("#sig-pad"), //canvas element as jQuery use $sigPadEle[0] to get DOM canvas
			$acceptSig = $("#sig-accept"), //button to accept drawing
			$cancelSig = $("#sig-cancel"), //"X" close signature pad
			$clearSig  = $("#sig-clear"),
			sigIsDirty = false; //button to clear signature pad

		//Set canvas width & height **setting this in css distorts functionality


		//Initiate signature pad
		var sigPad = new SignaturePad($sigPadEle[0],{
			minWidth: 1.5,
			maxWidth: 4,
			dotSize : 4,
			onEnd: function(){
				sigHasData(true);
			}
		});

		/* Start Events */
		$(".sig-btn").click(function(){
			var $t = $(this),
				tgt = $t.attr('data-tgt'),
				ratio = 2;
			if($t.attr('data-ratio') !== undefined && $t.attr('data-ratio') !== ''){
				ratio = parseFloat($t.attr('data-ratio'));
			}
			$sigModal.attr('data-ratio',ratio);
			$t.text("CHANGE");
			$t.next().show();
			$sigPadEle.attr("data-size","normal");
			showSigPad(tgt,true);
			return false;
		});
		$(".sig-btn-small").click(function(){
			var $t = $(this),
				tgt = $t.attr('data-tgt');
			$t.text("CHANGE");
			$t.next().show();
			$sigPadEle.attr("data-size","small");
			showSigPad(tgt,true);
			return false;
		});

		$(".sig-rem").click(function(){
			var $t = $(this),
				$tgt = $t.attr('data-tgt'),
				$img = $('#' + $(this).attr('data-tgt')),
				$inp = $('#' + $(this).attr('data-tgt') + '-inp');
			if(confirm("Remove this signature?")){
				$t.hide();
				$img.attr('src','data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=');
				$inp.val("");
			}

		});

		$("#sig-accept").click(function(){
			var tgt = $('#' + $(this).attr('data-tgt')),
				inp = $('#' + $(this).attr('data-tgt') + '-inp'),
				sm  = $sigPadEle.attr("data-size") == "small";
			if(tgt.length > 0) {
				if(sigIsDirty){
					if(sm){
						var $tempCv = $("<canvas id=\"temp-c\" width=\"270\" height=\"135\"></canvas>");
						var ctx = $tempCv[0].getContext('2d');
						ctx.drawImage($sigPadEle[0],0,0,270,135);
					}else{
						
						var $tempCv = $("<canvas id=\"temp-c\" width=\"450\" height=\"225\"></canvas>");
						var ctx = $tempCv[0].getContext('2d');
						ctx.drawImage($sigPadEle[0],0,0,450,225);
					}
					
					tgt.attr('src',$tempCv[0].toDataURL());
					inp.attr('data-hasdata',true);
					inp.val($tempCv[0].toDataURL());
				}
			}else{
				console.warn('tgt not found: '+ $(this).attr('data-tgt'));
			}
			$sigModal.modal('hide');
			return false;
		});

		$cancelSig.click(function(){
			$sigModal.modal('hide');
			return false;
		});

		$clearSig.click(function(){
			sigPad.clear();
			return false;
		});
		/*End Events */

	}
	
		/*Start Functions*/
		function sigHasData(bval){
			sigIsDirty = bval;		
		}
	
		function showSigPad(imgEleId,clear){
			$sigModal.one('shown.bs.modal',function(){
				sigHasData(false);
				setSigPadCanvasSize($sigPadEle);
			});
			if(clear !== false){//default - clears signature pad
				sigPad.clear();
				$acceptSig.attr('data-tgt',imgEleId);
				//$sigModal.show();
				$sigModal.modal('show');
			}
		}

		function setSigPadCanvasSize($ele){
			var $w = $ele.parent(),
				w,h,
				dim = calcSigDimensions($w);

			$ele.attr('height',dim.h);
			$ele.attr('width',dim.w);
			$("#sig-pad-btns").css('width',w);
			return true;
		}

		function calcSigDimensions($w){
			var dim = {
					w: 0,
					h: 0
				},
				$md = $w.closest('.modal'),
				ratio = 2,
				w_offset = $w.offset(),
				offset = {
					top: w_offset.top - $(window).scrollTop()
				},
				mh = $(window).height() - 74 - ($md.find('.modal-header').height()) - ($md.find('.modal-footer').height()) - offset.top - ($(window).height()/50),
				mw = $(window).width() - 60;
			if($md.attr('data-ratio') !== undefined && $md.attr('data-ratio') !== ''){
				ratio = parseFloat($md.attr('data-ratio'));
			}
			
			var h1 = mh,
				w1 = mh*ratio;

			if(w1 > mw){
				//set from height
				dim.w = mw;
				dim.h = mw/ratio;
			}else{
				//set from width
				dim.w = w1;
				dim.h = h1;
			}

			return dim;

		}
		/* End Functions */
});