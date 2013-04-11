
function flip (event)
{
	var element = event.currentTarget;
	/* Toggle the setting of the classname attribute */
	element.className = (element.className == 'card') ? 'card flipped' : 'card';
}
