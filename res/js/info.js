
// React components related to admin info
const INFO = (function () {
	const e = React.createElement;
	function Users(props) {
		if (props.users) {
			return e('span', { 'class': 'users', 'onClick': props.showUsers },
				e('i', { class: 'far fa-user' }), " ", props.users);
		} else {
			return "";
		}
	}

	function Views(props) {
		if (props.views) {
			return e('span', { 'class': 'views' }, e('i', { class: 'fas fa-eye' }), " ", props.views);
		} else {
			return "";
		}
	}

	function Time(props) {
		if (props.time) {
			return e('span', { 'class': 'time' }, e('i', { class: 'far fa-clock' }), " ", props.time);
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

	function byFirst(a, b) {
		return a.firstname.localeCompare(b.firstname);
	}

	function byLast(a, b) {
		return a.lastname.localeCompare(b.lastname);
	}

	function byHours(a, b) {
		return a.hours - b.hours;
	}

	function reverse(func, a, b) {
		return -func(a, b);
	}

	class ViewersHeader extends React.Component {
		constructor(props) {
			super(props);
			this.state = {
				sorted: "byHours",
				desc: true,
			};
		}

		click(name, func, orderFun) {
			func(orderFun);
			let order = "desc";
			if (this.state.sorted === name) {
				if (this.state.desc) {
					order = "asc"
				} else {
					order = "desc";
				}
			}
			this.setState({
				sorted: name,
				desc: order == "desc",
			});
		}

		render() {
			const headers = [];

			let firstClick = this.click.bind(this, 'byFirst', this.props.sort, byFirst);
			let lastClick = this.click.bind(this, 'byLast', this.props.sort, byLast);
			let hoursClick = this.click.bind(this, "byHours", this.props.sort, byHours);

			let firstSort = "fas fa-sort";
			let lastSort = "fas fa-sort";
			let hourSort = "fas fa-sort";

			if (this.state.sorted == "byFirst") {
				if (this.state.desc) {
					firstSort = "fas fa-sort-up";
					firstClick = this.click.bind(this, 'byFirst', this.props.sort, reverse.bind(null, byFirst));
				} else {
					firstSort = "fas fa-sort-down";
				}
			} else if (this.state.sorted == "byLast") {
				if (this.state.desc) {
					lastSort = "fas fa-sort-up";
					lastClick = this.click.bind(this, 'byLast', this.props.sort, reverse.bind(null, byLast));
				} else {
					lastSort = "fas fa-sort-down";
				}
			} else if (this.state.sorted == "byHours") {
				if (this.state.desc) {
					hourSort = "fas fa-sort-up";
					hoursClick = this.click.bind(this, "byHours", this.props.sort, reverse.bind(null, byHours));
				} else {
					hourSort = "fas fa-sort-down";
				}
			}

			const firstIcon = e('i', { class: firstSort });
			const lastIcon = e('i', { class: lastSort });
			const hourIcon = e('i', { class: hourSort });

			headers.push(e('th', { onClick: firstClick }, 'Given Names', firstIcon));
			headers.push(e('th', { onClick: lastClick }, 'Family Names', lastIcon));
			headers.push(e('th', { onClick: hoursClick }, 'Hours', hourIcon));
			return e('tr', null, headers);
		}
	}

	function ViewersRow(props) {
		const cols = [];
		cols.push(e('td', null, props.firstname));
		cols.push(e('td', null, props.lastname));
		cols.push(e('td', null, props.hours));
		return e('tr', null, cols);
	}

	class ViewersTable extends React.Component {
		constructor(props) {
			super(props);
			this.state = { users: props.users };
		}

		sort(order) {
			console.log("sorting");
			const sorted = this.state.users.sort(order);
			this.setState({ users: sorted });
		}

		render() {
			const caption = e('caption', null, this.props.title);
			const rows = [
				e(ViewersHeader, {
					sort: this.sort.bind(this),
				}),
			];
			for (const user of this.state.users) {
				rows.push(ViewersRow(user));
			}
			const tbody = e('tbody', null, rows);
			return e('table', null, caption, tbody);
		}
	}

	return { Users, Views, Time, Info, ViewersTable };
})();

