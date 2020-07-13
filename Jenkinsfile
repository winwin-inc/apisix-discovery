def buildStateHasChanged = false;

pipeline {
    agent any
    stages {
        stage('build') {
            steps {
                sh '''
composer72 install
winner lint src
php72 -d memory_limit=-1 vendor/bin/phpunit tests
                '''
            }
        }
        
        stage('deploy') {
            steps {
                sh '''
if [ "$GIT_BRANCH" = "test" ]; then
    composer72 install --no-dev
    rm -f *.gz
    composer72 package
    tars patch `grep 'app=' config.conf.example | head -1 | awk -F= '{print $2}'` *.gz
fi
                '''
            }
        }
    }

    post {
        success {
            script {
                if (buildStateHasChanged == true) {
                    echo "Notify for success because build state has changed..."
                    sendNotification('SUCCESS')
                }
            }
        }
        failure {
            sendNotification('FAILURE')
        }
        changed {
            echo "Build state has changed..."
            script {
                buildStateHasChanged = true
            }
        }
    }    
}

def sendNotification(status) {
    sh "jenkins-notify -j ${env.JOB_NAME} -s ${status} -u ${env.BUILD_URL}"
}
