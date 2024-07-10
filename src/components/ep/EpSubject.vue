<template>
  <v-form ref="form">
    <v-card class="mx-auto">
      <v-card-title class="text-h5">
        {{ title }}
      </v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="12" md="6">
            <v-text-field name="subject[name]" v-model="data.name" outlined label="Название" placeholder="Название" :rules="rules.name"></v-text-field>
            <v-text-field name="subject[sort]" v-model="data.sort" outlined label="Sort" placeholder="Sort" :rules="rules.sort" :type="'number'"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-select :items="teachers" label="Учителя" @change="addTeacher" ref="teachers"></v-select>
            <v-simple-table>
              <template v-slot:default>
                <tbody>
                <tr v-for="(item, index) in data.users" :key="index">
                  <td>
                    {{item.name}}
                    <input type="hidden" :name="'subject[users]['+index+'][user_id]'" :value="item.user_id">
                  </td>
                  <td>
                    <v-btn small color="warning" title="Удалить" @click="removeTeacher(index)">
                      <v-icon>
                        mdi-delete
                      </v-icon>
                    </v-btn>
                  </td>
                </tr>
                </tbody>
              </template>
            </v-simple-table>
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-btn class="mr-4" color="green" @click="save(true)">
          Сохранить
        </v-btn>
        <v-btn class="mr-4" color="primary" @click="save(false)">
          Применить
        </v-btn>
        <v-btn @click="$router.back();">
          Назад
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-form>
</template>

<script>
let ii = 0;
export default {
  name: "EpSubject",
  computed: {
    title () {
      if (this.data.id) {
        return 'Предмет "' + this.oldName + '"';
      } else {
        return "Новый предмет";
      }
    }
  },
  data: () => ({
    oldName: "",
    rules: {
      name: [
        v => !!v || 'Name is required',
      ],
      sort: [],
    },
    teachers: [],
    data: {
      id: null,
      name: "",
      sort: 100
    }
  }),
  methods: {
    save (back) {
      const isNew = !this.data.id;
      const formData = new FormData(this.$refs.form.$el);
      formData.append('f', "save");
      if (!isNew) {
        formData.append('subject[id]', this.data.id);
      }
      return this.$http.post('/ep/subject.php', formData).then((function (data) {
        if (isNew) {
          this.$store.dispatch('snackbar/message', 'Предмет "' + data.name + '", успешно создан!');
        } else {
          this.$store.dispatch('snackbar/message', 'Предмет "' + data.name + '", успешно изменен!');
        }
        this.oldName = this.data.name;
        if (back) {
          this.$router.go(-1);
        } else {
          this.data = data;
          if (!this.data.users) {
            this.$set(this.data, 'users', {})
          }
        }
      }).bind(this));
    },
    load () {
      const formData = new FormData();
      formData.append('f', "load");
      formData.append('subject[id]', this.$route.params.id);
      return this.$http.post('/ep/subject.php', formData).then((function (data) {
        this.data = data;
        this.oldName = this.data.name;
        if (!this.data.users) {
          this.$set(this.data, 'users', {})
        }
      }).bind(this));
    },
    loadTeachers () {
      const formData = new FormData();
      formData.append('f', "load-teachers");
      return this.$http.post('/ep/subject.php', formData).then((function (data) {
        this.teachers = data;
      }).bind(this));
    },
    addTeacher (val) {
      let notAppend = false;
      let addItem = {
        id: 'n'+(ii++),
        user_id: val,
        name: ""
      };
      this.teachers.forEach(function (item) {
        if (item.value == val) {
          addItem.name = item.text;
        }
      });
      Object.keys(this.data.users).forEach((function (key) {
        notAppend = notAppend || this.data.users[key].user_id == val;
      }).bind(this));
      if (notAppend) {
        this.$store.dispatch('snackbar/message', 'Нельзя добавить "' + addItem.name + '"! Уже есть!');
      } else {
        this.$set(this.data.users, addItem.id, addItem)
      }
      this.$refs.teachers.reset();
    },
    removeTeacher (val) {
      this.$delete(this.data.users, val);
    }
  },
  mounted () {
    this.load()
      .finally((function () {
        return this.loadTeachers();
      }).bind(this));
  }
}
</script>

<style scoped>

</style>