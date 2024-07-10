<template>
  <v-container fluid>
      <v-sheet height="64">
        <v-toolbar flat>
          <v-toolbar-title>{{name}}</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn outlined class="mr-4" color="grey darken-2" @click="focus = ''">
            Today
          </v-btn>
          <v-btn fab text small color="grey darken-2" @click="prev">
            <v-icon small>
              mdi-chevron-left
            </v-icon>
          </v-btn>
          <v-btn fab text small color="grey darken-2" @click="next">
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
              <v-toolbar-title v-html="selectedEvent.name + ' ' + selectedEvent.fStart"></v-toolbar-title>
              <v-spacer></v-spacer>
              <v-btn icon @click="selectedOpen = false">
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </v-toolbar>
            <br />
            <v-card-title v-html="selectedEvent.theme"></v-card-title>
            <v-card-subtitle>
              <v-row>
                <v-col cols="2">
                  Учитель:
                </v-col>
                <v-col cols="10" v-html="selectedEvent.teacher"></v-col>
              </v-row>
              <v-row>
                <v-col cols="2">
                  Адресс почты:
                </v-col>
                <v-col cols="10">
                  <a :href="'mailto:'+selectedEvent.email">{{selectedEvent.email}}</a>
                </v-col>
              </v-row>
            </v-card-subtitle>
            <v-card-text>
              <p v-if="selectedEvent.ht"><b>Домашнее задание:</b><br />{{selectedEvent.ht}}</p>
              <p v-if="selectedEvent.des">
                <b>Комментарий к заданию:</b><br />
                <span v-html="selectedEvent.des"></span>
              </p>
              <p v-if="selectedEvent.pt">
                <b>Практическое задание:</b><br />
                <span v-html="selectedEvent.pt"></span>
              </p>
              <template v-if="selectedEvent.net && selectedEvent.net.length">
                <hr />
                <p>
                  <b>Интернет-ресурсы:</b><br />
                  <span v-for="(link, index) in selectedEvent.net" :key="index">
                    <a :href="link" target="_blank">{{link}}</a><br />
                  </span>
                </p>
              </template>
              <template v-if="selectedEvent.files">
                <hr />
                <a v-for="(link, name) in selectedEvent.files" :key="name" :href="link" target="_blank" class="mr-3">{{name}}</a>
              </template>
            </v-card-text>
            <!--<v-card-actions>
              <v-btn text color="secondary">
                Cancel
              </v-btn>
            </v-card-actions>-->
          </v-card>
        </v-dialog>
      </v-sheet>
  </v-container>
</template>

<script>
export default {
  name: "EpCalendar",
  data: () => ({
    ready: false,
    focus: '',
    name: '',
    type: 'week',
    weekdays: [1, 2, 3, 4, 5, 6],
    selectedEvent: {},
    selectedElement: null,
    selectedOpen: false,
    loading: true,
    events: [],
    colors: ['blue', 'green', 'orange'],
  }),
  mounted () {
    this.$refs.calendar.checkChange()
    this.loadInfo();
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
    fetchEvents ({ start, end }) {
      const formData = new FormData();
      formData.append('f', "load");
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
    prev () {
      this.$refs.calendar.prev()
    },
    next () {
      this.$refs.calendar.next()
    },

    showEvent ({ nativeEvent, event }) {
      this.selectedOpen = true
      this.loading = true;
      this.selectedEvent = event
      this.selectedElement = nativeEvent.target
      this.loadDetail(this.selectedEvent.id).then((function (result) {
        this.loading = false;
        Object.assign(this.selectedEvent, result);
        this.selectedEvent.fStart = (new Date(this.selectedEvent.start.replace(" ", "T")+":00")).toLocaleString();
      }).bind(this));
      nativeEvent.stopPropagation()
    },
    loadDetail (id) {
      const formData = new FormData();
      formData.append('f', "load-detail");
      formData.append('id', id);
      return this.$http.post('/event/event.php', formData);
    },
    loadInfo () {
      const formData = new FormData();
      formData.append('f', "load-info");
      formData.append('class_id', this.$route.params.id);
      return this.$http.post('/event/event.php', formData).then((function (res) {
        this.name = res.name;
      }).bind(this));
    }
  },
}
</script>

<style scoped>

</style>