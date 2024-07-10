<template>
  <v-container fluid>
      <v-sheet height="64">
        <v-toolbar flat>
          <v-toolbar-title>{{name}}</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn outlined class="mr-4" color="grey darken-2" @click="focus = ''">
            Сегодня
          </v-btn>
          <v-btn fab text small color="grey darken-2" @click="$refs.calendar.prev()">
            <v-icon small>
              mdi-chevron-left
            </v-icon>
          </v-btn>
          <v-btn fab text small color="grey darken-2" @click="$refs.calendar.next()">
            <v-icon small>
              mdi-chevron-right
            </v-icon>
          </v-btn>
          <v-toolbar-title v-if="$refs.calendar">
            {{ $refs.calendar.title }}
          </v-toolbar-title>
        </v-toolbar>
      </v-sheet>
      <v-sheet height="600">
        <v-calendar
            first-time="07:00:00"
            v-model="focus"
            ref="calendar"
            color="primary"
            :events="events"
            :event-color="getEventColor"
            :type="type"
            :weekdays="weekdays"
            @click:event="showEvent"
            @click:time="newEvent"
            @change="fetchEvents"
        >
          <template v-slot:day-body="{ date, week }">
            <div
                class="current-time"
                :class="{ first: date === week[0].date }"
                :style="{ top: nowY }"
            ></div>
          </template>
        </v-calendar>
        <v-dialog v-model="selectedOpen" :activator="selectedElement" origin="top center" max-width="900">
          <v-card color="grey lighten-4" min-width="350px" flat :loading="loading">
            <v-toolbar :color="selectedEvent.color" dark>
              <v-toolbar-title v-html="selectedEvent.name"></v-toolbar-title>
              <v-spacer></v-spacer>
              <v-btn icon @click="selectedOpen = false">
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </v-toolbar>
            <br />
            <v-card-text>
              <v-form ref="form">
                <input type="hidden" name="event[class_id]" :value="selectedEvent.class_id" />
                <v-select name="event[group_id]" :disabled="!!selectedEvent.id" v-model="selectedEvent.group_id" outlined :items="subGroups" label="Группа"></v-select>
                <v-select v-if="selectedEvent.id" name="event[user_id]" v-model="selectedEvent.user_id" outlined :items="teachers" label="Учитель"></v-select>
                <template v-if="!selectedEvent.id">
                  <v-card-subtitle>Урок</v-card-subtitle>
                  <v-btn-toggle ref="times">
                    <v-btn v-for="(val) in [1, 2, 3, 4, 5, 6, 7, 8]" :key="val" @click="setTime(val)">
                      <v-icon>{{ val }}</v-icon>
                    </v-btn>
                  </v-btn-toggle>
                </template>
                <v-datetime-picker
                    :text-field-props="textFieldProps('event[start]')"
                    :time-picker-props="timePickerProps"
                    timeFormat="HH:mm:ss"
                    v-model="selectedEvent.start"
                    label="Начало"
                    placeholder="Начало"
                > </v-datetime-picker>
                <v-datetime-picker
                    :text-field-props="textFieldProps('event[end]')"
                    :time-picker-props="timePickerProps"
                    timeFormat="HH:mm:ss"
                    v-model="selectedEvent.end"
                    label="Конец"
                    placeholder="Конец"
                > </v-datetime-picker>
                <hr />
                <template v-if="selectedEvent.id">
                  <v-radio-group v-model="selectedEvent.editType" column>
                    <v-radio name="event[editType]" label="Только это" value="one"></v-radio>
                    <v-radio name="event[editType]" label="Все последущие" value="after"></v-radio>
                    <v-radio name="event[editType]" label="Все" value="all"></v-radio>
                  </v-radio-group>
                </template>
                <template v-else>
                  <v-dialog ref="dialog" v-model="modal" :return-value.sync="selectedEvent.repeate" width="290px">
                    <template v-slot:activator="{ on, attrs }">
                      <v-text-field name="event[repeate]" v-model="selectedEvent.repeate" label="Повторять до" placeholder="Повторять до" prepend-icon="mdi-calendar" readonly v-bind="attrs" v-on="on"></v-text-field>
                    </template>
                    <v-date-picker v-model="selectedEvent.repeate" scrollable>
                      <v-spacer></v-spacer>
                      <v-btn text color="primary" @click="selectedEvent.repeate = null; $refs.dialog.save(selectedEvent.repeate); modal = false; ">
                        Clear
                      </v-btn>
                      <v-btn text color="primary" @click="$refs.dialog.save(selectedEvent.repeate)">
                        OK
                      </v-btn>
                    </v-date-picker>
                  </v-dialog>
                </template>
              </v-form>
            </v-card-text>
            <v-card-actions>
              <v-btn color="green" @click="save">
                Сохранить
              </v-btn>
              <v-spacer></v-spacer>
              <v-btn v-if="selectedEvent.id" color="red" @click="remove">
                Удалить
              </v-btn>
              <v-spacer></v-spacer>
              <v-btn text color="secondary" @click="selectedOpen = false">
                Отмена
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-sheet>
  </v-container>
</template>

<script>
export default {
  name: "EpCalendarEditor",
  data: () => ({
    ready: false,
    name: '',
    focus: '',
    type: 'week',
    events: [],
    weekdays: [1, 2, 3, 4, 5, 6],
    modal: false,
    selectedEvent: {
      editType: 'one'
    },
    teachers: [],
    selectedElement: null,
    selectedOpen: false,
    loading: true,
    subGroups: [],
    colors: ['blue', 'green', 'orange'],
    timePickerProps: {
      ampmInTitle: true,
      format: "24hr"
    },
    times: {
      '1': ["8:50", "9:40", "10:30", "11:20", "12:10", "13:00", "13:45", "14:30"],
      def: ["8:00", "8:55", "9:50", "10:45", "11:40", "12:35", "13:25", "14:15"]
    }
  }),
  mounted () {
    this.$refs.calendar.checkChange()
    this.loadInfo();
    this.loadSubGroup();

    this.ready = true;
    this.scrollToTime();
    this.updateTime();
  },
  computed: {
    cal() {
      return this.ready ? this.$refs.calendar : null;
    },
    nowY() {
      let t = new Date();
      return this.cal
          ? this.cal.timeToY({ hour: t.getHours(), minute: t.getMinutes() }) + 'px'
          : '-10px';
    },
  },
  methods: {
    getCurrentTime() {
      return this.cal
          ? this.cal.times.now.hour * 60 +
          this.cal.times.now.minute
          : 0;
    },
    scrollToTime() {
      const time = this.getCurrentTime();
      const first = Math.max(0, time - (time % 30) - 30);
      this.cal.scrollToTime(first);
    },
    updateTime() {
      setInterval(() => this.cal.updateTimes(), 60 * 1000);
    },

    textFieldProps (name) {
      name = name || ""
      return {
        name: name,
        type: 'datetime'
      }
    },
    fetchEvents ({ start, end }) {
      const formData = new FormData();
      formData.append('f', "load-editor");
      formData.append('class_id', this.$route.params.id);
      formData.append('start', `${start.date} 00:00:00`);
      formData.append('end', `${end.date} 23:59:59`);
      this.$http.post('/event/event.php', formData).then((function (result) {
        this.events = result
      }).bind(this));
    },
    getEventColor (event) {
      return event.color
    },

    showEvent ({ nativeEvent, event, eventParsed }) {
      this.selectedOpen = true
      this.loading = true;
      this.selectedEvent = event
      this.selectedEvent.index = eventParsed.index
      this.selectedElement = nativeEvent.target
      this.loadDetail(this.selectedEvent.id).then((function (result) {
        this.loading = false;
        Object.assign(this.selectedEvent, result);
        this.selectedEvent.editType = "one";
        this.loadTeachers(this.selectedEvent.subject_id)
      }).bind(this));
      nativeEvent.preventDefault();
      nativeEvent.stopPropagation();
    },
    save () {
      const formData = new FormData(this.$refs.form.$el);
      if (this.selectedEvent.id) {
        formData.append('f', "edit-event");
        formData.append('event[id]', this.selectedEvent.id);
      } else {
        formData.append('f', "add-event");
      }
      this.$http.post('/event/event.php', formData).then((function (result) {
        Object.assign(this.selectedEvent, result, {timed: true});
        this.selectedOpen = false;
      }).bind(this));
    },
    remove () {
      const formData = new FormData(this.$refs.form.$el);
      formData.append('f', "remove-event");
      if (this.selectedEvent.id) {
        formData.append('event[id]', this.selectedEvent.id);
      }
      return this.$http.post('/event/event.php', formData).then((function () {
        let start = new Date(this.selectedEvent.start)
        let now = new Date()
        if (start > now) {
          this.$delete(this.events, this.selectedEvent.index);
        }
        this.selectedOpen = false;
      }).bind(this));
    },
    loadDetail (id) {
      const formData = new FormData();
      formData.append('f', "load-editor-detail");
      formData.append('id', id);
      return this.$http.post('/event/event.php', formData);
    },
    loadTeachers (subject_id) {
      const formData = new FormData();
      formData.append('f', "load-teachers");
      formData.append('subject_id', subject_id);
      return this.$http.post('/event/event.php', formData).then((function (res) {
        this.teachers = res;
      }).bind(this));
    },
    loadInfo () {
      const formData = new FormData();
      formData.append('f', "load-info");
      formData.append('class_id', this.$route.params.id);
      return this.$http.post('/event/event.php', formData).then((function (res) {
        this.name = res.name;
      }).bind(this));
    },
    loadSubGroup () {
      const formData = new FormData();
      formData.append('class_id', this.$route.params.id);
      formData.append('f', "load-sub-groups");
      return this.$http.post('/event/event.php', formData).then((function (res) {
        this.subGroups = res;
      }).bind(this));
    },
    roundTime (time, down = true) {
      const roundTo = 5 // minutes
      const roundDownTime = roundTo * 60 * 1000
      return down
          ? time - time % roundDownTime
          : time + (roundDownTime - (time % roundDownTime))
    },
    newEvent (tms) {
      let mouse = new Date(tms.year, tms.month - 1, tms.day, tms.hour, tms.minute).getTime()
      const start = new Date(this.roundTime(mouse));
      mouse = mouse + 40*60*1000;
      const end = new Date(this.roundTime(mouse));
      this.selectedEvent = {
        name: `Event #${this.events.length}`,
        class_id: this.$route.params.id,
        editType: 'one',
        color: "orange",
        start: start,
        end: end,
        repeate: null,
        timed: true
      };
      this.selectedEvent.index = this.events.push(this.selectedEvent)-1;
      this.selectedOpen = true
      this.loading = false;
    },
    setTime (val) {
      let time = (this.times[this.selectedEvent.start.getDay()] || this.times.def)[val-1].split(':');
      let mouse = new Date(
          this.selectedEvent.start.getFullYear(),
          this.selectedEvent.start.getMonth(),
          this.selectedEvent.start.getDate(),
          time[0], time[1], 0
      ).getTime()
      const start = new Date(this.roundTime(mouse));
      mouse = mouse + 40*60*1000;
      const end = new Date(this.roundTime(mouse));
      this.selectedEvent.start = start;
      this.selectedEvent.end = end;
    }
  },
}
</script>

<style scoped>

</style>