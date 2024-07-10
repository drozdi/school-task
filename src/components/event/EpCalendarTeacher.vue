<template>
  <v-row class="fill-height">
    <v-col>
      <v-sheet height="64">
        <v-toolbar flat>
          <v-btn outlined class="mr-4" color="grey darken-2" @click="focus = ''">
            Today
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
        <v-dialog v-model="selectedOpen" :activator="selectedElement" fullscreen max-width="900">
          <v-card color="grey lighten-4" min-width="350px" flat :loading="loading">
            <v-toolbar :color="selectedEvent.color" dark>
              <v-toolbar-title>
                {{selectedEvent.name}}
                <v-spacer></v-spacer>
                {{selectedEvent.fStart}}
              </v-toolbar-title>
              <v-spacer></v-spacer>
              <v-btn icon @click="selectedOpen = false">
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </v-toolbar>
            <v-card-text>
              <v-form ref="form">
                <v-container>
                  <v-row>
                    <v-col md="6">
                      <v-textarea
                          label="Тема урока"
                          placeholder="Тема урока"
                          name="event[theme]"
                          rows="2"
                          v-model="selectedEvent.theme"
                      ></v-textarea>
                    </v-col>
                    <v-col md="6">
                      <v-textarea
                          label="Практическое задание"
                          placeholder="Практическое задание"
                          name="event[pt]"
                          rows="2"
                          v-model="selectedEvent.pt"
                      ></v-textarea>
                    </v-col>
                  </v-row>
                  <v-row>
                    <v-col md="6">
                      <v-textarea
                          label="Интернет-ресурсы"
                          placeholder="Интернет-ресурсы"
                          name="event[netResource]"
                          rows="2"
                          v-model="selectedEvent.netResource"
                      ></v-textarea>
                    </v-col>
                    <v-col md="6">
                      <v-textarea
                          label="Домашнее задание"
                          placeholder="Домашнее задание"
                          name="event[ht]"
                          rows="2"
                          v-model="selectedEvent.ht"
                      ></v-textarea>
                    </v-col>
                  </v-row>
                  <v-row>
                    <v-col>
                      <div class="text-h5">Комментарий к заданию</div>
                      <!--<v-textarea
                          label="Комментарий к заданию"
                          placeholder="Комментарий к заданию"
                          name="event[description]"
                          rows="2"
                          v-model="selectedEvent.description"
                          class="__ck-editor",
                          id="form_description"
                      ></v-textarea>-->
                      <ckeditor :editor="editor" v-model="selectedEvent.description"></ckeditor>
                    </v-col>
                  </v-row>
                  <v-row>
                    <v-col>
                      <v-file-input name="files[]" multiple label="Загрузить файлы"></v-file-input>
                      <v-simple-table>
                        <template v-slot:default>
                          <tbody>
                            <tr v-for="(item, index) in selectedEvent.files" :key="index">
                              <td>
                                <input type="hidden" name="files[]" :value="item.id">
                                {{item.name}}
                              </td>
                              <td>
                                <v-btn color="red" @click="removeFile(index, $event)">
                                  <v-icon>
                                    mdi-close
                                  </v-icon>
                                </v-btn>
                              </td>
                            </tr>
                          </tbody>
                        </template>
                      </v-simple-table>
                    </v-col>
                  </v-row>
                </v-container>
              </v-form>
            </v-card-text>
            <v-card-actions>
              <v-btn color="green" @click="save">
                Save
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-sheet>
    </v-col>
  </v-row>
</template>

<script>
import Editor from '@/plugins/ckeditor';

export default {
  name: "EpCalendarTeacher",
  data: () => ({
    editor: Editor,
    ready: false,
    focus: '',
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
      formData.append('f', "load-teacher");
      formData.append('start', `${start.date}T00:00:00`);
      formData.append('end', `${end.date}T23:59:59`);
      this.$http.post('/event/event.php', formData).then((function (result) {
        this.events = result
      }).bind(this));
    },
    getEventColor (event) {
      return event.color
    },

    showEvent ({ nativeEvent, event }) {
      this.loading = true;
      this.selectedEvent = event
      this.selectedElement = nativeEvent.target
      this.loadDetail(this.selectedEvent.id).then((function (result) {
        this.loading = false;
        this.selectedEvent = Object.assign(this.selectedEvent, result);
        this.selectedEvent.fStart = (new Date(this.selectedEvent.start.replace(" ", "T")+":00")).toLocaleString();
        this.selectedOpen = true
      }).bind(this));
      nativeEvent.stopPropagation()
    },
    loadDetail (id) {
      const formData = new FormData();
      formData.append('f', "load-teacher-detail");
      formData.append('id', id);
      return this.$http.post('/event/event.php', formData);
    },
    save () {
      const formData = new FormData(this.$refs.form.$el);
      formData.append('f', "save");
      formData.append('event[id]', this.selectedEvent.id);
      formData.append('event[description]', this.selectedEvent.description);
      return this.$http.post('/event/event.php', formData).then((function () {
        this.selectedEvent.color = this.selectedEvent.theme? "green": "blue";
        this.selectedOpen = false;
      }).bind(this));
    },
    removeFile (index, $event) {
      this.$delete(this.selectedEvent.files, index);
      $event.target.closest('tr').remove();
    }
  },
}
</script>

<style scoped>

</style>