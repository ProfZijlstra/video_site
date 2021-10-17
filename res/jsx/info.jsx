// React components related to admin info
const INFO = (function () {
    const loc = window.location + "";
    let enrollment = false;
    function setEnrollment(enr) { enrollment = enr; }

    function Users(props) {
        if (props.users) {
            return (
                <span class="users" onClick={props.showUsers}>
                    <i class="far fa-user"></i> {props.users}
                </span>
            );
        } else {
            return "";
        }
    }

    function Views(props) {
        if (props.views) {
            return (
                <span class="views">
                    <i class="fas fa-eye"></i> {props.views}
                </span>
            );
        } else {
            return "";
        }
    }

    function Time(props) {
        if (props.time) {
            return (
                <span class="time">
                    <i class="far fa-clock"></i> {props.time}
                </span>
            );
        } else {
            return "";
        }
    }

    function Info(props) {
        if (props.users) {
            return (
                <div>
                    <Users users={props.users} showUsers={props.showUsers}></Users>
                    &nbsp;<Views views={props.views}></Views>
                    &nbsp;<Time time={props.time}></Time>
                </div>
            );
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
                sorted: "byHours",
                desc: false,
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

            return (
                <tr>
                    <th onClick={firstClick}>Given Names <i class={firstSort}></i></th>
                    <th onClick={lastClick}>Family Names <i class={lastSort}></i></th>
                    <th onClick={pdfClick}>PDF <i class={pdfSort}></i></th>
                    <th onClick={videoClick}>Video <i class={videoSort}></i></th>
                    <th onClick={hoursClick}>Hours <i class={hoursSort}></i></th>
                    <th onClick={longClick}>Too Long <i class={longSort}></i></th>
                    <th onClick={hoursLongClick}>Inc Long<i class={hoursLongSort}></i></th>
                </tr>
            );
        }
    }

    function ViewersRow(props, day) {
        return (
            <tr>
                <td><a href={"views/" + props.id + (day ? `#${day}` : '')}>{props.firstname}</a></td>
                <td><a href={"views/" + props.id + (day ? `#${day}` : '')}>{props.lastname} </a></td>
                <td class="num">{props.pdf}</td>
                <td class="num">{props.video}</td>
                <td class="num">{props.hours}</td>
                <td class="num">{props.too_long}</td>
                <td class="num">{props.hours_long}</td>
            </tr>
        );
    }

    class ViewersTable extends React.Component {
        constructor(props) {
            super(props);
            this.state = { users: props.users };
        }

        sort(order) {
            const sorted = this.state.users.sort(order);
            this.setState({ users: sorted });
        }

        newRows(users) { this.setState({ users: users }); }

        render() {
            let rows = this.state.users.map(u => ViewersRow(u, this.props.day));
            return (
                <table>
                    <caption>{this.props.title}</caption>
                    <tbody>
                        <ViewersHeader sort={this.sort.bind(this)}></ViewersHeader>
                        {rows}
                    </tbody>
                </table>
            );
        }
    }

    function showTables(title, users, day) {
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
        if (enrolled.length) {
            items.push(<ViewersTable title="Enrolled Users" users={enrolled} day={day}></ViewersTable>);
        }
        if (enrol_nv.length) {
            items.push(<ViewersTable title="Enrolled No View" users={enrol_nv} day={day}></ViewersTable>);
        }
        if (non_enrol.length) {
            items.push(<ViewersTable title="Non-Enrolled Users" users={non_enrol} day={day}></ViewersTable>);
        }

        const combined = (
            <div>
                <h2>{title}</h2>
                {items}
            </div>
        );

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
            .then(json => showTables(title, json, day));
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
        fetch(`${num}/viewers?day_id=${day_id}&video=${encodeURIComponent(video)}`)
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
