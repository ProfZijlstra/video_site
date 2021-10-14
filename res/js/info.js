
// React components related to admin info
const INFO = (function() {
    const e = React.createElement;
    const loc = window.location + "";
    let day = false;
    if (day = loc.match(/W\dD\d/)) {
        day = day[0];
    }

    let enrollment = false;
    function setEnrollment(enr) { enrollment = enr; }

    function Users(props) {
        if (props.users) {
            return e('span', {'class' : 'users', 'onClick' : props.showUsers},
                     e('i', {class : 'far fa-user'}), " ", props.users);
        } else {
            return "";
        }
    }

    function Views(props) {
        if (props.views) {
            return e('span', {'class' : 'views'},
                     e('i', {class : 'fas fa-eye'}), " ", props.views);
        } else {
            return "";
        }
    }

    function Time(props) {
        if (props.time) {
            return e('span', {'class' : 'time'},
                     e('i', {class : 'far fa-clock'}), " ", props.time);
        } else {
            return "";
        }
    }

    function Info(props) {
        if (props.users) {
            return e('div', props, Users(props), " ", Views(props), " ",
                     Time(props));
        } else {
            return "";
        }
    }

    function byFirst(a, b) { return a.firstname.localeCompare(b.firstname); }

    function byLast(a, b) { return a.lastname.localeCompare(b.lastname); }

    function byPdf(a, b) { return a.pdf - b.pdf; }

    function byVideo(a, b) { return a.video - b.video; }

    function byHours(a, b) { return a.hours - b.hours; }

    function byLong(a, b) { return a.too_long - b.too_long; }

    function byHoursLong(a, b) { return a.hours_long - b.hours_long; }

    function reverse(func, a, b) { return -func(a, b); }

    class ViewersHeader extends React.Component {
        constructor(props) {
            super(props);
            this.state = {
                sorted : "byHours",
                desc : false,
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
                sorted : name,
                desc : order == "desc",
            });
        }

        render() {
            const headers = [];

            let firstClick =
                this.click.bind(this, 'byFirst', this.props.sort, byFirst);
            let lastClick =
                this.click.bind(this, 'byLast', this.props.sort, byLast);
            let pdfClick =
                this.click.bind(this, 'byPdf', this.props.sort, byPdf);
            let videoClick =
                this.click.bind(this, 'byVideo', this.props.sort, byVideo);
            let hoursClick =
                this.click.bind(this, "byHours", this.props.sort, byHours);
            let longClick =
                this.click.bind(this, "byLong", this.props.sort, byLong);
            let hoursLongClick = this.click.bind(this, "byHoursLong",
                                                 this.props.sort, byHoursLong);

            let firstSort = "fas fa-sort";
            let lastSort = "fas fa-sort";
            let pdfSort = "fas fa-sort";
            let videoSort = "fas fa-sort";
            let hoursSort = "fas fa-sort";
            let longSort = "fas fa-sort";
            let hoursLongSort = "fas fa-sort";

            if (this.state.sorted == "byFirst") {
                if (this.state.desc) {
                    firstSort = "fas fa-sort-up";
                    firstClick =
                        this.click.bind(this, 'byFirst', this.props.sort,
                                        reverse.bind(null, byFirst));
                } else {
                    firstSort = "fas fa-sort-down";
                }
            } else if (this.state.sorted == "byLast") {
                if (this.state.desc) {
                    lastSort = "fas fa-sort-up";
                    lastClick = this.click.bind(this, 'byLast', this.props.sort,
                                                reverse.bind(null, byLast));
                } else {
                    lastSort = "fas fa-sort-down";
                }
            } else if (this.state.sorted == "byPdf") {
                if (this.state.desc) {
                    pdfSort = "fas fa-sort-up";
                    pdfClick = this.click.bind(this, 'byPdf', this.props.sort,
                                               reverse.bind(null, byPdf));
                } else {
                    pdfSort = "fas fa-sort-down";
                }
            } else if (this.state.sorted == "byVideo") {
                if (this.state.desc) {
                    videoSort = "fas fa-sort-up";
                    videoClick =
                        this.click.bind(this, 'byVideo', this.props.sort,
                                        reverse.bind(null, byVideo));
                } else {
                    videoSort = "fas fa-sort-down";
                }
            } else if (this.state.sorted == "byHours") {
                if (this.state.desc) {
                    hoursSort = "fas fa-sort-up";
                    hoursClick =
                        this.click.bind(this, "byHours", this.props.sort,
                                        reverse.bind(null, byHours));
                } else {
                    hoursSort = "fas fa-sort-down";
                }
            } else if (this.state.sorted == "byLong") {
                if (this.state.desc) {
                    longSort = "fas fa-sort-up";
                    longClick = this.click.bind(this, "byLong", this.props.sort,
                                                reverse.bind(null, byLong));
                } else {
                    longSort = "fas fa-sort-down";
                }
            } else if (this.state.sorted == "byHoursLong") {
                if (this.state.desc) {
                    hoursLongSort = "fas fa-sort-up";
                    hoursLongClick =
                        this.click.bind(this, "byHoursLong", this.props.sort,
                                        reverse.bind(null, byHoursLong));
                } else {
                    hoursLongSort = "fas fa-sort-down";
                }
            }

            const firstIcon = e('i', {class : firstSort});
            const lastIcon = e('i', {class : lastSort});
            const pdfIcon = e('i', {class : pdfSort});
            const videoIcon = e('i', {class : videoSort});
            const hoursIcon = e('i', {class : hoursSort});
            const longIcon = e('i', {class : longSort});
            const hoursLongIcon = e('i', {class : hoursLongSort});

            headers.push(e('th', {key : "th_first", onClick : firstClick},
                           'Given Names', firstIcon));
            headers.push(e('th', {key : "th_last", onClick : lastClick},
                           'Family Names', lastIcon));
            headers.push(
                e('th', {key : "th_pdf", onClick : pdfClick}, 'PDF', pdfIcon));
            headers.push(e('th', {key : "th_video", onClick : videoClick},
                           'Video', videoIcon));
            headers.push(e('th', {key : "th_hours", onClick : hoursClick},
                           'Hours', hoursIcon));
            headers.push(e('th', {key : "th_long", onClick : longClick},
                           'Too Long', longIcon));
            headers.push(e('th',
                           {key : "th_hoursLong", onClick : hoursLongClick},
                           'Inc Long', hoursLongIcon));
            return e('tr', null, headers);
        }
    }

    function ViewersRow(props) {
        const cols = [];
        cols.push(
            e('td', {key : `${props.id}-first`},
              e('a', {href : `views/${props.id}` + (day ? `#${day}` : '')},
                props.firstname)));
        cols.push(
            e('td', {key : `${props.id}-last`},
              e('a', {href : `views/${props.id}` + (day ? `#${day}` : '')},
                props.lastname)));
        cols.push(e('td', {key : `${props.id}-pdf`, class : "num"}, props.pdf));
        cols.push(
            e('td', {key : `${props.id}-video`, class : "num"}, props.video));
        cols.push(
            e('td', {key : `${props.id}-hours`, class : "num"}, props.hours));
        cols.push(
            e('td', {key : `${props.id}-long`, class : "num"}, props.too_long));
        cols.push(e('td', {key : `${props.id}-hoursLong`, class : "num"},
                    props.hours_long));
        return e('tr', {key : `${props.id}-row`}, cols);
    }

    class ViewersTable extends React.Component {
        constructor(props) {
            super(props);
            this.state = {users : props.users};
        }

        sort(order) {
            const sorted = this.state.users.sort(order);
            this.setState({users : sorted});
        }

        newRows(users) { this.setState({users : users}); }

        render() {
            const caption = e('caption', null, this.props.title);
            const rows = [
                e(ViewersHeader,
                  {sort : this.sort.bind(this), key : "header-row"}),
            ];
            for (const user of this.state.users) {
                rows.push(ViewersRow(user));
            }
            const tbody = e('tbody', null, rows);
            return e('table', null, caption, tbody);
        }
    }

    function showTables(title, users) {
        const tables = document.getElementById("content");
        const overlay = document.getElementById("overlay");
        ReactDOM.unmountComponentAtNode(tables);

        // deep clone enrollment object
        const myEnrollment = JSON.parse(JSON.stringify(enrollment));
        const enrolled = [];
        const enrol_nv = [];
        const non_enrol = [];
        for (const user of users) {
            if (myEnrollment[user.id]) {
                enrolled.push(user);
                myEnrollment[user.id].seen = true;
            } else {
                non_enrol.push(user);
            }
        }
        for (const id in myEnrollment) {
            const user = myEnrollment[id];
            if (!user.seen) {
                enrol_nv.push(user);
            }
        }

        const items = [];
        const header = React.createElement('h2', null, title);
        items.push(header);

        if (enrolled.length) {
            items.push(React.createElement(ViewersTable, {
                title : "Enrolled Users",
                users : enrolled,
            },
                                           null));
        }
        if (enrol_nv.length) {
            items.push(React.createElement(ViewersTable, {
                title : "Enrolled No View",
                users : enrol_nv,
            },
                                           null));
        }
        if (non_enrol.length) {
            items.push(React.createElement(ViewersTable, {
                title : "Non-Enrolled Users",
                users : non_enrol,
            },
                                           null));
        }
        const combined = React.createElement('div', null, items);

        ReactDOM.render(combined, tables);
        overlay.classList.add("visible");
    }

    function hideTables() {
        document.getElementById("overlay").classList.remove("visible");
    }

    function offeringViewers() {
        const offering_id = document.getElementById('offering').dataset.id;
        const course = document.getElementById("course_num").textContent;
        const offering = document.getElementById("offering").textContent;
        const title = `${course} ${offering}`;
        fetch(`viewers?offering_id=${offering_id}`)
            .then(response => response.json())
            .then(json => showTables(title, json));
    }

    function dayViewers(evt) {
        let elm = evt.target.parentNode;
        while (!elm.dataset.day) {
            elm = elm.parentNode;
        }
        const day = elm.dataset.day;
        const day_id = elm.dataset.day_id;
        const text = elm.dataset.text;
        const title = `${day} ${text}`;
        fetch(`${day}/viewers?day_id=${day_id}`)
            .then(response => response.json())
            .then(json => showTables(title, json));
    }

    function videoViewers(evt) {
        const day_id = document.getElementById('day').dataset.id;
        let elm = evt.target.parentNode;
        while (!elm.dataset.show) {
            elm = elm.parentNode;
        }
        const video = elm.dataset.show;
        const title = video.substring(3);
        const num = video.substring(0, 2);
        fetch(`${num}/viewers?day_id=${day_id}&video=${
                  encodeURIComponent(video)}`)
            .then(response => response.json())
            .then(json => showTables(title, json));
    }

    return {
        setEnrollment,
        Info,
        offeringViewers,
        dayViewers,
        videoViewers,
        hideTables,
    };
})();
