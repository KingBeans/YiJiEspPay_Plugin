
	<tr id="tr{{ OrderId|raw }}" class="GridRow {% if order.deleted %}orderGridDeleted{% endif %}" onmouseover="$(this).addClass('GridRowOver').removeClass('GridRow')" onmouseout="$(this).addClass('GridRow').removeClass('GridRowOver')">
		<td align="center" style="width:23px">
			<input type="checkbox" name="orders[]" value="{{ OrderId1|raw }}" class="exportSelectableItem" />
		</td>
		<td align="center" style="width:15px">
			<a href="#" onclick="QuickView('{{ OrderId|raw }}'); return false;"><img id="expand{{ OrderId|raw }}" src="images/plus.gif" align="left" width="19" class="ExpandLink" height="16" title="{% lang 'ExpandQuickView' %}" border="0"></a>
		</td>
		<td align="center" style="width:18px">
			<img src="images/{{ OrderIcon|raw }}" width="16" height="16" />
		</td>
		<td class="orderGridOrderId {{ SortedFieldIdClass|raw }}">
			<span class="orderIdText" {% if order.deleted %}title="{% lang 'deletedOrderToolTip'  %}"{% endif %}>{{ OrderId|raw }}</span>
			{% if order.deleted %}<span class="orderDeletedText">({% lang 'deleted' %})</span>{% endif %}
		</td>
		<td colspan="{{ CustomerNameSpan|raw }}" class="{{ SortedFieldCustClass|raw }}">
			{{ CustomerLink|raw }}
		</td>
		<td class="{{ SortedFieldDateClass|raw }}">
			{{ Date|raw }}
		</td>
		<td id="order_status_column_{{ OrderId|raw }}" style="border-left-style: solid; border-left-width: 10px; width:165px;" class="{{ SortedFieldStatusClass|raw }} OrderStatus OrderStatus{{ OrderStatusId|raw }}" nowrap="nowrap">
			<select {% if order.deleted %}disabled="disabled" title="{% lang 'OrderDeletedStatusChangeNotice' %}"{% endif %} onclick="order_status_before_change=this.selectedIndex; status_box=this" id="status_{{ OrderId|raw }}" name="status_{{ OrderId|raw }}" class="Field" onchange="update_order_status('{{ OrderId|raw }}', this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)">
				{{ OrderStatusOptions|raw }}
			</select>
			<img id="ajax_status_{{ OrderId|raw }}" src="images/ajax-blank.gif" />
			<div class="{{ PaymentStatusColor|raw }}" style="{{ HidePaymentStatus|raw }}">
				{{ PaymentStatus|raw }}
			</div>
		</td>
		<td style="text-align: center; display: {{ HideOrderMessages|raw }}" class="{{ SortedFieldMessageClass|raw }}">
			{{ MessageLink|raw }}
		</td>
		<td style="text-align: right;" class="{{ SortedFieldTotalClass|raw }}">
			{{ Total|raw }}
		</td>
		<td nowrap="nowrap" align="right">
			{{ NotesIcon|raw }}
			{{ CommentsIcon|raw }}
		</td>
		<td align="center" class="{{ FlagCellClass|raw }}" style="width: 18px; display: {{ HideCountry|raw }}">
			{{ OrderCountryFlag|raw }}
		</td>
		<td>
			<select name="order_options_{{ OrderId|raw }}" id="order_action_{{ OrderId|raw }}" onchange="Order.HandleAction('{{ OrderId|raw }}', $(this).val());">
				<option value="">-- {% lang 'Actions' %} --</option>
				<option value="editOrder">{% lang 'EditOrder' %}</option>
				<option value="printInvoice">{% lang 'PrintInvoice' %}</option>
				<option value="printPackingSlip">{% lang 'PrintPackingSlip' %}</option>
				<option value="orderNotes" class="{{ HasNotesClass|raw }}">{% lang 'OrderNotesLink' %}</option>
				{{ ShipItemsLink|raw }}
				{% if order.ordtotalshipped > 0 %}
					<option value="viewShipments">{{ lang.ViewShipments }}</option>
				{% endif %}
				{{ DelayedCaptureLink|raw }}
				{{ VoidLink|raw }}
				{{ RefundLink|raw }}
				{% if order.ordpaymentstatus == 'authorizing' and order.orderpaymentmethod == 'YijiPay' %}
					<option value="authorizingAction">{{ order.ordpaymentstatus|raw }}</option>
				{% endif %}

			</select>
		</td>
	</tr>
	<tr id="trQ{{ OrderId|raw }}" style="display:none">
		<td></td>
		<td colspan="12" id="tdQ{{ OrderId|raw }}" class="QuickView"></td>
	</tr>
