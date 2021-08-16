
// React components related to admin info
const INFO = (function() {
	const e = React.createElement;
	function Users(props) {
		if (props.users) {
			return e('span', {'class': 'users'}, e('i', {class: 'far fa-user'}), " ", props.users);
		} else {
			return "";
		}
	}

	function Views(props) {
		if (props.views) {
			return e('span', {'class': 'views'}, e('i', {class: 'fas fa-eye'}), " ", props.views);
		} else {
			return "";
		}
	}

	function Time(props) {
		if (props.time) {
			return e('span', {'class': 'time'}, e('i', {class: 'far fa-clock'}), " ", props.time);
		} else {
			return "";
		}
	}

	function Info(props) {
		if (props.users) {
			return e('div', props, Users(props), " ", Views(props), " ", Time(props));
		} else {
			return "";
		}
	}

	return { Users, Views, Time, Info };
})();

