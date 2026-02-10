<style type="text/css">
	.contract-container {
		font-family: 'Roboto', sans-serif;
	}
	
	.io-heading {
		font-size: large;
	}
	
	.divTable {
		display: table;
		width: 100%;
		page-break-inside: auto;
		border: 1px solid black;
		border-collapse: collapse;
	}
	
	.divTableRow {
		display: table-row;
		page-break-inside: avoid;
	}
	
	.divTableHeading {
		/*background-color: #EEE;*/
		border-bottom: 4px solid black;
		font-weight: bold;
	}
	
	.divTableCell,
	.divTableHead {
		border: 1px solid black;
		display: table-cell;
		padding: 3px 10px;
	}
	
	.divTableFoot {
		background-color: #EEE;
		display: table-footer-group;
		font-weight: bold;
	}
	
	.divTableBody {
		display: table-row-group;
	}
	
	.right {
		text-align: right;
	}
	
	.center {
		text-align: center;
	}

</style>
<style>
	p {
		margin: 0 0 0px;
	}
	
	.ctr-terms ol > li::marker {
		font-weight: bold;
	}

</style>
<div class="contract-container">

	<table style="text-align: right; display: table;" width="100%">
		<tbody>
			<tr>
				<td>

					<p><img src="https://rental.software/users/DsJumpers/images/LOGO - DSJUMPERS_979149.jpg" align="left" border="0" style="margin-right: 10px; width: 188px;"></p>
					<div style="text-align: left;">

						<p>
							<br>
							<br><strong>*company_name*</strong>
							<br>*company_address*
							<br><strong>Phone:</strong> *company_phone*</p>
					</div>
				</td>
				<td>

					<p><strong>Invoice:&nbsp;</strong>*leadid*</p>

					<p><strong>Order Date:&nbsp;</strong>*contractsentdate*</p>
				</td>
			</tr>
		</tbody>
	</table>

	<table align="middle" border="0" cellpadding="1" cellspacing="1" style="width: 100%;">
		<tbody>
			<tr>
				<td align="left" style="width: 60%;">

					<p><u>Event Location &amp; Renter</u>
						<br><strong>*organization*</strong>
						<br><strong>*ctfirstname* *ctlastname*</strong> ~~empty(*venuename*)~ ~
						<br>*venuename*~~
						<br>*eventstreet*
						<br>*eventcity*, *eventstate* *eventzip*
						<br>*phones*</p>
				</td>
				<td align="center" style="width: 40%;">
					<div style="text-align: justify;">

						<p><strong>Start Date:</strong> *startdate* *starttime*
							<br><strong>End Date:</strong> *enddate* *endtime*
							<br><strong>Delivery method:</strong> *deliverytype*</p>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<div class="divTable">
		<div class="divTableBody">
			<div class="divTableRow divTableHeading">
				<div class="divTableCell">

					<p><strong>Name</strong></p>
				</div>
				<div class="divTableCell center">

					<p><strong>Qty</strong></p>
				</div>
				<div class="divTableCell right">

					<p><strong>Total</strong></p>
				</div></div>
			<div class="divTableRow" id="repeat_start_rentals">
				<div class="divTableCell">

					<p style="~~!empty(*rental_is_heading*)~text-align:center~ ~~;">*rentalimage* *rentalname* *fullrentaltime*</p>
				</div>
				<div class="divTableCell center">

					<p style="~~!empty(*rental_is_heading*)~display:none~ ~~;">*rentalqty*</p>
				</div>
				<div class="divTableCell right">

					<p style="~~!empty(*rental_is_heading*)~display:none~ ~~;">*rentaltotalprice*</p>

					<p>*rental_discount_display*</p>
				</div></div>
			<div class="divTableRow" id="repeat_end_rentals" style="display:none;">
				<div class="divTableCell">&nbsp;</div></div></div></div>

	<table border="0" cellpadding="1" cellspacing="0" style="width: 100%;">
		<tbody>
			<tr>
				<td style="width: 40%;">

					<p><strong>Rentals subtotal</strong></p>
				</td>
				<td style="width: 20%;">
					<br>
				</td>
				<td style="width: 20%; text-align: right;">
					<br>
				</td>
				<td align="right" style="width: 20%; text-align: right;">

					<p>&nbsp;$*subtotal*</p>
				</td>
			</tr>
			<tr id="repeat_start_fees">
				<td style="width: 40%;"><strong>*feesname*</strong></td>
				<td style="width: 20%;">&nbsp;</td>
				<td style="width: 20%; text-align: right;">*feestaxed*</td>
				<td align="right" style="width: 20%; text-align: right;">$*feesprice*</td>
			</tr>
			<tr id="repeat_end_fees" style="display:none;">
				<td colspan="4">
					<br>
				</td>
			</tr>
			<tr>
				<td style="width: 40%;">

					<p><strong>Sales Tax</strong></p>
				</td>
				<td style="width: 20%;">
					<br>
				</td>
				<td style="width: 20%; text-align: right;">*taxrate*%</td>
				<td align="right" style="width: 20%; text-align: right;">

					<p>&nbsp;$*salestax*</p>
				</td>
			</tr>
			<tr>
				<td style="width: 40%; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: black;">

					<p><strong>Total</strong></p>
				</td>
				<td style="width: 20%; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: black;">
					<br>
				</td>
				<td style="width: 20%; text-align: right; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: black;">
					<br>
				</td>
				<td align="right" style="width: 20%; text-align: right; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: black;">

					<p><strong>$*total*</strong></p>
				</td>
			</tr>
			<tr>
				<td style="width: 40%;">

					<p><strong>Deposit Due&nbsp;</strong></p>
				</td>
				<td style="width: 20%;">
					<br>
				</td>
				<td style="width: 20%; text-align: right;">
					<br>
				</td>
				<td style="width: 20%; text-align:right;">

					<p>$*ctr_deposit_due*</p>
				</td>
			</tr>
			<tr>
				<td style="width: 40%;">

					<p><strong>Amount Paid&nbsp;</strong></p>
				</td>
				<td style="width: 20%;">
					<br>
				</td>
				<td style="width: 20%; text-align: right;">
					<br>
				</td>
				<td style="width: 20%; text-align:right;">

					<p>$*ctr_amount_paid*</p>
				</td>
			</tr>
			<tr>
				<td style="width: 40%;">

					<p><strong>Balance Due&nbsp;</strong></p>
				</td>
				<td style="width: 20%;">
					<br>
				</td>
				<td style="width: 20%; text-align: right;">
					<br>
				</td>
				<td style="width: 20%; text-align:right;">

					<p>$*ctr_balance_due*</p>
				</td>
			</tr>
		</tbody>
	</table>
	<div align="left" style="text-align: left;">

		<p>*eventnotes*</p>
	</div>
	<div align="center">
		<br>
	</div>
	<div style="font-size: 1px; page-break-after: always; height: 1px; background-color: rgb(192, 192, 192);" contenteditable="false" title="Page Break"></div>

	&nbsp;

	<p style="text-align: center;">
		&nbsp; &nbsp; &nbsp; <strong>RENTAL AGREEMENT AND GENERAL RELEASE</strong>&nbsp; &nbsp;&nbsp;</p>
	<div class="ctr-terms" style="font-size: 9pt; margin-bottom: 4px;">

		<p style="text-align: justify;">
			&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
			<br>In consideration of the Rental Equipment described on the invoice page of this Rental Agreement and General Release, the parties agree to the following: &nbsp; &nbsp; &nbsp; &nbsp;</p>

		<ol>
			<li style="list-style-type:decimal;"><strong>Identity of parties:</strong> For this Rental Agreement, Company shall mean *company_name*, its owners, officers, directors, employees, contractors, and agents. &quot;Customer&quot; shall mean the person(s) listed in the &quot;Event Location &amp; Renter&quot; section on the invoice page of this agreement as well as the person signing the agreement (if different), and their agents and/or employees.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Equipment, Rent, Payment, and Term of Rental Agreement:</strong> Customer rents from Company certain equipment described on the invoice page of this agreement. The rental fee set forth is payable, in full, in advance, and the rental term shall be that listed as &quot;Start Time&quot; on the invoice page of this agreement, but all of Customer&#39;s obligations arising under the terms and conditions of this Rental Agreement shall run from the actual delivery of the rental equipment to the actual pickup of the rental equipment by Company. If the equipment is delivered and accepted by Customer, then Customer shall not be entitled to any refund whatsoever if Customer elects not to use the equipment for reasons not covered in other provisions of this agreement.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Operation:</strong> Customer agrees to provide <span style="text-decoration:underline;">&nbsp; *electric* &nbsp;</span> electrical outlet(s) rated at 115 volts with 20 amperes capacity per motor unit within 50 feet of each equipment. No electrical cords are to be used that are not supplied by Company. If the blower stops or the air pressure is low, remove all users immediately, and then check on the problem. The electrical cord should be plugged into an outlet and be the only thing operating on that electrical circuit. &nbsp;Circuit breakers should also be checked. &nbsp;The customer is subject to an additional charge for all service calls due to electricity.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>&nbsp;Weather:</strong> Company does not guarantee weather conditions. We reserve the right to cancel or reschedule your rental before delivery if severe weather conditions are imminent or if we have reason to believe that the equipment and/or its users may be in danger. We also reserve the right to pick up the equipment early should weather conditions change. If we pick up the equipment early, Customer will be entitled to reschedule the rental on another day within 30 days. Some examples of severe weather conditions are extreme temperatures, high winds, rain, snow, thunder, and/or lightning.<strong>In the event of severe weather during a rental, Customer agrees that they will unplug the inflatable, allow it to deflate, and not use the equipment until severe weather ends.</strong></li>
			<br>
			<li style="list-style-type:decimal;"><strong>Return Check Policy:</strong> If a check is returned to Company for insufficient funds, Customer agrees to pay the total rental balance as well as an additional $75.00 return fee.
				<br>
				<br>
			</li>
			<li style="list-style-type:decimal;"><strong>Care of the Rental Equipment:</strong> Customer shall be responsible for any damage to any of the Rental Equipment not caused by ordinary wear and tear. &quot;Ordinary wear and tear&quot; shall mean only the normal deterioration of the rental equipment caused by ordinary, reasonable, and proper use of the rental equipment. Customer shall be liable to Company for all damage, which is not &quot;ordinary wear and tear&quot; including, but is not limited to, cutting or tearing of vinyl or netting, damage due to overturning, overloading, exceeding rated capacities, breakage, improper use, abuse, lack of cleaning, contamination of or dirtying of rental equipment with non-approval items such as chemicals, food, paint, silly string, mud, clay, or other materials. Customer will also be liable for damage done by weather if the damage could have been prevented by Customer deflating the equipment as covered in article three (3) above.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Warranties:</strong> Company makes no warranties either expressed or implied as to the condition or performance of any equipment and/or property leased by Customer from Company. By signing this contract, Customer agrees that any warranty of merchantability or fitness for a particular purpose is hereby disclaimed. By signing this contract, Customer agrees that no expressed warranty as to the condition or performance of any equipment and/or property leased by Customer is hereby disclaimed. Customer understands that the only warranties about said equipment and/or property is that which is stated in the instruction manual for said equipment and/or property, which Customer has received a copy of.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Possession:</strong> Customer&#39;s right to possession of the Rental Equipment begins upon the items being delivered to Customer&#39;s premises and terminates on the actual pick up by Company. Retention of possession or any failure to permit the pickup of the equipment at or after the end of the &quot;Rental period&quot; specified constitutes a material breach of this agreement. If the equipment is not returned for any reason, including theft, the Customer is obligated to pay to Company the full replacement value for such equipment listed on the invoice page of this agreement, plus all incidental costs associated with the attempted pick up or recovery of the equipment by Company. Customer shall not cause nor permit these items, or any of them, to be sublet, rented, sold, or removed from the delivery address, or otherwise transfer such items. If rental items are not returned and/or levied upon for any reason whatsoever, Company may take possession of said items without further notice or legal process and use whatever force is necessary to do so. Customer hereby agrees to indemnify, defend, and hold Company harmless from all claims and costs arising from such retaking. If rental items are stolen, or otherwise moved from the delivery address, Customer shall notify Company immediately.
				<br>
				<br>

				<ol>
					<li style="list-style-type:upper-alpha;"><strong>General Misuse:</strong> Do not allow riders to play or climb on walls, sides, or roof of inflatables. Do not allow water or a water hose near a dry inflatable. If the inflatable should become wet, have an adult wipe down equipment before riders return. Make sure the equipment is not wet when riders return, with the exception of units designed to have wet areas such as slip and slides, water slides, dunk tanks, etc.</li>
					<br>
					<li style="list-style-type:upper-alpha;"><strong>Negligence or Abuse:</strong> The following fees may be assessed for negligence or abuse of inflatable: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;

						<ol style="margin-top:0;margin-bottom:0;padding-inline-start:48px;">
							<li style="list-style-type:lower-roman;">Spilled Food, drink, or the uses of foreign substances $50</li>
							<li style="list-style-type:lower-roman;">Negligence or damage to the equipment(s): Full Repair Cost</li>
							<li style="list-style-type:lower-roman;">Damage beyond repair: Full Replacement Cost</li>
							<li style="list-style-type:lower-roman;">Excessive Cleaning: $50</li>
							<br>
						</ol>
					</li>
				</ol>
			</li>
			<li style="list-style-type:decimal;"><strong>Rules of Operation:</strong> Equipment must be operated over a smooth, compatible surface such as grass or hard top surface. The equipment may NOT be operated on rough surfaces such as rocks, brick, glass, or any jagged objects. Equipment may also not be operated over any exposed irrigation or utility lines or access points for such lines. The equipment cannot be moved by Customer after being placed by Company employees/contractors. &nbsp;Equipment MUST BE properly anchored before use. Equipment will be anchored initially by Company employees/contractors and the anchors MUST NOT be removed during the period of use. Never attempt to relocate, adjust, or service a blower. Never use during high winds, gusty winds, thunderstorms, or lightning. &nbsp;The equipment can turn over in high winds, even if anchored, and this could result in severe injuries to the users. &nbsp;Do not resume use until adverse weather conditions have ceased. Always follow the manufacturer&#39;s guidelines located on the equipment itself.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Overnight Rental:</strong> Unless otherwise agreed, equipment will be picked up on the same day it is dropped off. Customer agrees that any overnight rental will be in a secured location and that the equipment will be deflated, covered, and blowers removed to a secure indoor location when not in use.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Equipment problems:</strong> Should any equipment develop a problem, or not function correctly at any time, or Customer does not understand the operating instructions, the Customer agrees to immediately cease use of the equipment. In particular, if the inflatable equipment begins to deflate, Customer will immediately have the riders exit the equipment and then check for one of the following conditions: 1) The motor has stopped; in which case check the power cord connection at the outlet where the equipment plugs in to make sure that it has not been unplugged; 2) If the motor continues to run, check for blockage of the air intake screen on the side of the blower unit. Also, check both air tubes on the back of the equipment for snugness and tighten the ties if necessary; 3) If either of these steps corrects the problem, fully re-inflate the equipment before permitting anyone to use the equipment; 4) If you cannot correct the problem, call our office.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Pick-up Inspection</strong>: If Customer chooses to deflate the equipment before the arrival of the pick-up attendant, it must be re-inflated before it is packed up. The equipment will be inspected and may receive a preliminary cleaning before removal.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Cancellation/Refund Policy:</strong> Customer will only receive a full refund of the deposit if Customer cancels due to rain or inclement weather conditions before delivery. Inclement weather is the only variable that will necessitate a refund. A REFUND WILL NOT BE ISSUED for any other reason except inclement weather. The deadline for weather cancellations is no later than 8 AM on the date of the rental to receive a refund. If the equipment malfunctions or is inoperable, it is the sole responsibility of the Customer to notify Company immediately. If Company is not notified and given a chance to correct the problem, NO REFUND will be issued.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Release of Liability:</strong> Customer understands and acknowledges that play on an amusement device entails both known and unknown risks including, but not limited to, physical injury from falling, slipping, crashing or colliding, emotional injury, paralysis, distress, damage or death to any participant. Customer agrees to indemnify and hold Company harmless from any and all claims, actions, suits, proceedings, costs, expenses, fees, damages and liabilities, including, but not limited to, reasonable attorney&#39;s fees and costs, arising by reason of injury, damage, or death to persons or property, in connection with or resulting from the use of the leased equipment. This includes, but is not limited to, the manufacture, selection, delivery, possession, use, operation, or return of the equipment. Customer hereby releases and holds harmless Company from injuries or damages incurred as a result of the use of the leased equipment. Company cannot, under any circumstances, be held liable for injuries as a result of inappropriate use, God, nature, or other conditions beyond its control or knowledge. Customer also agrees to indemnify and hold harmless Company from any loss, damage, theft or destruction of the equipment during the term of the lease and any extensions thereof.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Rules and Supervision:</strong> Participants must be supervised at ALL times. Please go over all the rules to participants before using the equipment. Customer agrees to supervise both the equipment and its use at all times the equipment is in the possession of Customer. The Customer assumes the role of operator while equipment is in their possession. Accompanying the contract is a set of Manufacturer directions for use.
				<br>
				<br>

				<ol>
					<li style="list-style-type:upper-alpha;">Customer is responsible for enforcing posted rules, rules listed in this document, and any attached rules/code of conduct provided. Customer is responsible for ensuring that the size and number of persons entering the equipment does not exceed the manufacturer&#39;s maximum occupancy.</li>
					<br>
					<li style="list-style-type:upper-alpha;">Unless otherwise specified by the manufacturer, Customer agrees to have at least 1 person of average strength per equipment at all times. This person (Customer/operator) will be responsible for the operation of the rented equipment.</li>
					<br>
					<li style="list-style-type:upper-alpha;">Instructions for safety and operation will be reviewed at time of setup and include but are not limited to:

						<ol>
							<li style="list-style-type:lower-roman;">NO SHOES in inflatables.</li>
							<li style="list-style-type:lower-roman;">SOCKS REQUIRED when indicated.</li>
							<li style="list-style-type:lower-roman;">All persons must remove shoes, glasses, jewelry, belts, sharp objects etc.</li>
							<li style="list-style-type:lower-roman;">NO flips or somersaults etc.</li>
							<li style="list-style-type:lower-roman;">NO roughhousing, horseplay in/or around equipment.</li>
							<li style="list-style-type:lower-roman;">NO climbing, hanging, standing or pulling on sides, tops, backs of equipment</li>
							<li style="list-style-type:lower-roman;">NO crawling under bottom or edge of equipment.</li>
							<li style="list-style-type:lower-roman;">NO taping, fastening or hanging anything to or in the inflatable equipment. Severe injury risk can occur.</li>
							<li style="list-style-type:lower-roman;">NO FOOD, DRINKS, CANDY, ANIMALS, SHOES, SILLY STRING or SHARP OBJECTS are to be allowed in the rentals at any time.</li>
						</ol>
					</li>
				</ol>
			</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Customer Acknowledgement</strong>: Customer acknowledges and certifies that they have had sufficient opportunity to read this entire Agreement and agrees to be bound by all the terms and conditions on all pages and they understand its content and that they execute it freely, intelligently, and without duress of any kind.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Entire Agreement:</strong> This Agreement constitutes the full Agreement between Company and Customer. Any prior agreements, whether written or oral, promises, negotiations, or representations not expressly set forth herein shall be of no force or effect. Customer acknowledges the receipt of the Rental Equipment that is the subject of the Rental Agreement and General Release and the fact that it is in good working order.</li>
		</ol>

		<p style="text-align: justify;">BY SIGNING MY NAME ON THIS CONTRACT, I ACKNOWLEDGE THAT I HAVE COMPLETELY READ AND UNDERSTAND THIS CONTRACT, I WILL AGREE TO BE FULLY INSTRUCTED BY COMPANY PERSONNEL AS A TRAINED OPERATOR FOR THE AFOREMENTIONED EQUIPMENT AND HAVE HAD ALL OF MY QUESTIONS ANSWERED TO MY SATISFACTION. &nbsp;I UNDERSTAND THAT I AM MUTUALLY RESPONSIBLE FOR ADHERING TO THE TERMS IN THIS AGREEMENT.</p>
	</div>

	<div style="font-size: 9pt; margin-bottom: 4px;">

		
		<br>
	</div>

	<p style="font-size: 9pt;"><strong>Lessor will:</strong>
		<br>1. Provide the necessary staff to facilitate your event and power cords to reach a minimum of 50ft.
		<br>2. Deliver, set-up, tear-down, and operate all activities with/without volunteer staff.
		<br>3. Carry a liability insurance policy covering our services &amp; equipment.&nbsp;</p>

	<p style="font-size: 9pt;"><strong>Lessee &nbsp;will:</strong>
		<br>1. Provide <u>_*electric*__</u> 110volt/20amp electric circuits and 10/12 gauge cords for distances over 50ft.
		<br>2. Provide any required entrance and parking passes.
		<br>3. Provide a minimum of <u>_*volnum*__</u> adult volunteer(s) to operate the activities.&nbsp;</p>
	<div id="repeat_start_rentals">
		<br>
	</div>
	<div style="~~empty(*rentalcontract*)~display:none~ ~~;">

		<p style="font-size: 9pt;"><strong>Details for *rentalname*:</strong>
			<br>*rentalcontract*
			<br>
		</p>
	</div>
	<div id="repeat_end_rentals" style="display:none;">
		<br>
	</div>
	<br>
	<div align="center">

		<p><strong>I HAVE READ THIS CONTRACT AND AGREE &amp; UNDERSTAND THE CONTENT.</strong>
			<br>
		</p>
	</div>

	<table width="100%">
		<tbody>
			<tr>
				<td style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #000000; font-family: arial; font-size: 10pt;" valign="bottom" width="40%">*signature*</td>
				<td width="20%">
					<br>
				</td>
				<td style=" border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #000000; font-family: arial; font-size: 10pt;" valign="bottom">

					<p><strong>&nbsp;*signeddate*&nbsp;</strong>
						<br>
					</p>
				</td>
			</tr>
			<tr>
				<td width="40%">

					<p>&nbsp;Signature</p>
				</td>
				<td width="20%">

					<p>&nbsp;
						<br>
					</p>
				</td>
				<td>

					<p>&nbsp;Date&nbsp;</p>
				</td>
			</tr>
			<tr>
				<td style="vertical-align: top;">
					<br>
				</td>
			</tr>
			<tr>
				<td style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #000000; font-family: arial; font-size: 10pt;" valign="bottom" width="40%">

					<p><strong>&nbsp;*printed_name*</strong>
						<br>
					</p>
				</td>
			</tr>
			<tr>
				<td width="40%">

					<p>&nbsp;Printed Name</p>
				</td>
			</tr>
		</tbody>
	</table>
</div>
