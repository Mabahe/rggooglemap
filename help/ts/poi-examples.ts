# /***************************
#  *      TS example 
#  *    EXT rggooglemap
#  *    Georg Ringer
#  * http://www.rgooglemap.com/
#  ***************************/ 

plugin.tx_rggooglemap_pi1	
	
	poi {
		
			## TABLE NAME, e.g. tt_address
			tt_address {
				# for every field there is full stdWrap functionality available!
	
				# Add a t: only if the field phone is filled
				phone {
					stdWrap {
						noTrimWrap = |t: | <br />|
						required = 1
					}
				}
			
				# Add "E-mail: " if the field email is filled and link it to its own value
				email {
					setContentToCurrent = 1
					stdWrap {
						noTrimWrap = |E-mail:| <br />|
						required = 1
						typolink.parameter.current = 1
					}
				}
				
				# Add an image to the POI
				image {
					# set the current value to current
					setContentToCurrent = 1
					if.isTrue.current = 1
					stdWrap.cObject = IMAGE
					stdWrap.cObject {
						file {
							# set the path where the image is saved
							import = uploads/tx_staticregion/
							import.current = 1
							maxW = 60
							maxH = 60
						}
						params = style="float:right"
					}
				}
							
		}
		# END of table specific configuration
		
		## Generic way to create as many markers as you like by. 
		## The key of the cObj will be the used as ###GENERIC_XXXXX### (transformed to upper case)
		
		generic {
			# Create a link to the single view of this record
			# Marker will look like ###GENERIC_LINK### 
			# This is just an example, working but rggooglemap doesn't provide any single view for this link!
			
			link = TEXT
			link {
				typolink {
					useCacheHash = 1
					# Set it to field.pid or use parameter = 123
					parameter.field = pid
					returnLast = url
					additionalParams.cObject = COA
					additionalParams.cObject {
						# Add the uid of the record to the link
						20 = TEXT
						20 {
							field = uid
							wrap = &tx_example_pi1[record]=|
					}
					title.field = title
				}
			}
		}

		# end generic
	}
	# end poi

}
# end plugin