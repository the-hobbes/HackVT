//display a list of ingredients based on food category selection
				var ingredientSelector = function(str)
				{
					$('#ingredientSelector').empty(); //empty out the selector so only the desired elements show up

					//set up ingredient arrays by category
					var meats = new Array("Chicken", "Beef", "Lamb", "Veal", "Venison");
					var vegetables = new Array("Corn", "Tomatoes", "Carrots", "Cabbage", "Lettuce", "Kale");
					var fruits = new Array("Apples", "Raspberries", "Blueberries", "Strawberries");
					var dairy = new Array("Cheese", "Milk", "Yogurt");

					var targetCategories = Array(); //holder for the arrays of categories as determined by user selection
					var ingredients_in = str.split(','); //explode based on ","

					//loop through user-selected categories
					for (var i = 0; i < ingredients_in.length; i++) 
					{
						//add array to targetCategories based on user selection
    					switch(ingredients_in[i])
    					{
    						case 'Meat':
    							targetCategories = targetCategories.concat(meats);
	    						break; //end meat

    						case 'Vegetables':
    							targetCategories = targetCategories.concat(vegetables);
    							break; //end vegetables

    						case 'Fruits':
    							targetCategories = targetCategories.concat(fruits);
    							break; //end fruits

    						case 'Dairy':
    							targetCategories = targetCategories.concat(dairy);
    							break; //end dairy
    					}
    				}
					 
    				//add ingredients from each category to selector
    				for (var i = 0; i < targetCategories.length; i++)
    				{
    					$('#ingredientSelector').append($('<option>', { 
    						value: targetCategories[i], 
    						text : targetCategories[i] 
    					}));
    				}
				}

				//Function to determine which category of food you'd like to choose ingredients from 
				$("#categorySelector").change(function () {
		          var str = "";
		          changed = false;

		          $("select option:selected").each(function () {
		                str += $(this).text() + ",";
		              });
		          ingredientSelector(str);
		        })
		        .trigger('change');